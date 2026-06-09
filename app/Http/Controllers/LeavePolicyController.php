<?php

namespace App\Http\Controllers;

use App\Models\LeavePolicy;
use App\Models\LeavePolicyRule;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;

class LeavePolicyController extends Controller
{
    public function index()
    {
        $policies = LeavePolicy::with(['leaveType', 'rules'])->get();
        $leaveTypes = LeaveType::where('status', 'active')
            ->whereDoesntHave('policy')
            ->get();
            
        $departments = Department::where('status', 'active')->get();
        $locations = Location::where('status', 'active')->get();

        return view('leave.policies.index', compact('policies', 'leaveTypes', 'departments', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id|unique:leave_policies,leave_type_id',
            'annual_allocation' => 'required|numeric|min:0',
            'monthly_accrual' => 'required|boolean',
            'carry_forward_limit' => 'required|numeric|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'notice_period_days' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $policy = LeavePolicy::create($data);

        // Save demographic rules if specified
        $this->savePolicyRules($policy, $request);

        return redirect()->back()->with('success', 'Leave policy created successfully.');
    }

    public function update(Request $request, $id)
    {
        $policy = LeavePolicy::findOrFail($id);

        $data = $request->validate([
            'annual_allocation' => 'required|numeric|min:0',
            'monthly_accrual' => 'required|boolean',
            'carry_forward_limit' => 'required|numeric|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'notice_period_days' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $policy->update($data);

        // Delete existing rules and re-save
        $policy->rules()->delete();
        $this->savePolicyRules($policy, $request);

        return redirect()->back()->with('success', 'Leave policy updated successfully.');
    }

    protected function savePolicyRules(LeavePolicy $policy, Request $request)
    {
        // 1. Gender Rule
        if ($request->filled('rule_gender_values')) {
            LeavePolicyRule::create([
                'policy_id' => $policy->id,
                'rule_type' => 'gender',
                'rule_operator' => 'in',
                'rule_values' => $request->input('rule_gender_values'),
            ]);
        }

        // 2. Department Rule
        if ($request->filled('rule_department_values')) {
            LeavePolicyRule::create([
                'policy_id' => $policy->id,
                'rule_type' => 'department',
                'rule_operator' => 'in',
                'rule_values' => $request->input('rule_department_values'),
            ]);
        }

        // 3. Location Rule
        if ($request->filled('rule_location_values')) {
            LeavePolicyRule::create([
                'policy_id' => $policy->id,
                'rule_type' => 'location',
                'rule_operator' => 'in',
                'rule_values' => $request->input('rule_location_values'),
            ]);
        }
    }
}
