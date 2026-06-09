<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::with('policy')->get();
        return view('leave.types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'description' => 'nullable|string',
            'color' => 'required|string|max:10',
            'is_paid' => 'required|boolean',
            'status' => 'required|string|in:active,inactive',
        ]);

        LeaveType::create($data);

        return redirect()->back()->with('success', 'Leave type created successfully.');
    }

    public function update(Request $request, $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:10',
            'is_paid' => 'required|boolean',
            'status' => 'required|string|in:active,inactive',
        ]);

        $leaveType->update($data);

        return redirect()->back()->with('success', 'Leave type updated successfully.');
    }

    public function destroy($id)
    {
        $leaveType = LeaveType::findOrFail($id);

        if ($leaveType->requests()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete leave type because requests have already been logged under it.');
        }

        $leaveType->delete();

        return redirect()->back()->with('success', 'Leave type deleted successfully.');
    }
}
