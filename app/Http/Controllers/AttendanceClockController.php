<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClockPunchRequest;
use App\Services\ClockingService;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceClockController extends Controller
{
    protected $clockingService;

    public function __construct(ClockingService $clockingService)
    {
        $this->clockingService = $clockingService;
    }

    public function showTimecard()
    {
        if (Gate::denies('attendance.create')) {
            abort(403);
        }

        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        // Check if there is an active open session (clocked in but not clocked out)
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('attendance_date', 'desc')
            ->first();

        // Check if user already clocked out today (session completed)
        $todaySession = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->first();

        $shift = $user->employeeDetail ? $user->employeeDetail->shift : null;

        return view('attendance.punch', compact('activeSession', 'todaySession', 'shift'));
    }

    public function clockIn(ClockPunchRequest $request)
    {
        try {
            $user = auth()->user();
            $data = $request->validated();
            $data['ip_address'] = $request->ip();
            $data['device_info'] = $request->header('User-Agent');
            $data['method'] = 'web';

            $attendance = $this->clockingService->clockIn($user, $data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $attendance]);
            }

            return redirect()->back()->with('success', 'Clock-in recorded successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function clockOut(ClockPunchRequest $request)
    {
        try {
            $user = auth()->user();
            $data = $request->validated();
            $data['ip_address'] = $request->ip();
            $data['device_info'] = $request->header('User-Agent');
            $data['method'] = 'web';

            $attendance = $this->clockingService->clockOut($user, $data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $attendance]);
            }

            return redirect()->back()->with('success', 'Clock-out recorded successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
