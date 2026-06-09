<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ReviewLeaveRequest;
use App\Services\LeaveRequestService;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    /**
     * Display a listing of the resource (dashboard / lists).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            // Admin sees all
            $requests = LeaveRequest::with(['employee.employeeDetail.department', 'leaveType'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($user->hasPermissionTo('leave.approve')) {
            // Manager sees team requests
            $requests = LeaveRequest::with(['employee.employeeDetail.department', 'leaveType'])
                ->whereHas('employee.employeeDetail', function ($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Employee sees own
            $requests = LeaveRequest::with('leaveType')
                ->where('employee_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        if ($request->wantsJson()) {
            return response()->json($requests);
        }

        // Fetch balances for dashboard summary
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $user->id)
            ->get();

        return view('leave.index', compact('requests', 'balances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Gate::denies('leave.create')) {
            abort(403);
        }

        $user = auth()->user();

        // Fetch eligible leave types
        $leaveTypes = LeaveType::where('status', 'active')->get();
        $balances = LeaveBalance::where('employee_id', $user->id)->pluck('allocated_balance', 'leave_type_id');

        return view('leave.create', compact('leaveTypes', 'balances'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeaveRequest $request)
    {
        try {
            $user = auth()->user();
            $this->leaveRequestService->submitRequest($user, $request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Leave request submitted successfully.']);
            }

            return redirect()->route('leave.index')->with('success', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with(['employee.employeeDetail.department', 'leaveType', 'approvals.approver', 'statusHistory.user'])
            ->findOrFail($id);

        if (Gate::denies('view', $leaveRequest)) {
            abort(403);
        }

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $leaveRequest->employee_id)
            ->get();

        return view('leave.show', compact('leaveRequest', 'balances'));
    }

    /**
     * Process manager review (approve/reject).
     */
    public function review(ReviewLeaveRequest $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            $approverId = auth()->id();
            $status = $request->input('status');
            $remarks = $request->input('remarks');

            if ($status === 'approved') {
                $this->leaveRequestService->approveRequest($leaveRequest->id, $approverId, $remarks);
                $msg = 'Leave request approved successfully.';
            } else {
                $this->leaveRequestService->rejectRequest($leaveRequest->id, $approverId, $remarks);
                $msg = 'Leave request rejected.';
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('leave.index')->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel / withdraw request.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if (Gate::denies('cancel', $leaveRequest)) {
                abort(403, 'You are not authorized to cancel this request.');
            }

            // Lock & update status
            $this->leaveRequestService->rejectRequest($leaveRequest->id, auth()->id(), 'Cancelled by Employee.');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Leave request cancelled.']);
            }

            return redirect()->route('leave.index')->with('success', 'Leave request cancelled.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
