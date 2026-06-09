<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitCorrectionRequest;
use App\Http\Requests\ReviewCorrectionRequest;
use App\Services\CorrectionService;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceCorrectionController extends Controller
{
    protected $correctionService;

    public function __construct(CorrectionService $correctionService)
    {
        $this->correctionService = $correctionService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            // Admin sees all corrections
            $corrections = AttendanceCorrection::with(['user.employeeDetail.department', 'attendance'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($user->hasPermissionTo('attendance.correction.approve')) {
            // Manager sees only direct reports' corrections
            $corrections = AttendanceCorrection::with(['user.employeeDetail.department', 'attendance'])
                ->whereHas('user.employeeDetail', function ($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Employee sees only their own corrections
            $corrections = AttendanceCorrection::with('attendance')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        if ($request->wantsJson()) {
            return response()->json($corrections);
        }

        return view('attendance.corrections.index', compact('corrections'));
    }

    public function create(Request $request)
    {
        if (Gate::denies('attendance.correction.request')) {
            abort(403);
        }

        $date = $request->input('date', '');
        $attendance = null;
        if ($date) {
            $attendance = Attendance::where('user_id', auth()->id())
                ->where('attendance_date', $date)
                ->first();
        }

        return view('attendance.corrections.create', compact('date', 'attendance'));
    }

    public function store(SubmitCorrectionRequest $request)
    {
        try {
            $this->correctionService->submitCorrection(auth()->user(), $request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Correction request submitted successfully.']);
            }

            return redirect()->route('attendance.corrections.index')->with('success', 'Correction request submitted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $correction = AttendanceCorrection::with(['user.employeeDetail.department', 'attendance.shift'])->findOrFail($id);

        // Check if user is allowed to view
        $user = auth()->user();
        if (!$user->hasRole('Admin') && $correction->user_id !== $user->id) {
            $detail = $correction->user->employeeDetail;
            if (!$detail || $detail->manager_id !== $user->id) {
                abort(403);
            }
        }

        return view('attendance.corrections.show', compact('correction'));
    }

    public function review(ReviewCorrectionRequest $request, $id)
    {
        try {
            $correction = AttendanceCorrection::findOrFail($id);

            // Authorize action via policy
            if (Gate::denies('approve', $correction)) {
                abort(403, 'You are not authorized to review this correction.');
            }

            $status = $request->input('status');
            $managerId = auth()->id();

            if ($status === 'approved') {
                $this->correctionService->approveCorrection($id, $managerId);
                $msg = 'Correction request approved successfully.';
            } else {
                $reason = $request->input('rejection_reason', 'Rejected by Manager');
                $this->correctionService->rejectCorrection($id, $managerId, $reason);
                $msg = 'Correction request rejected.';
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('attendance.corrections.index')->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
