<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Models\UserLoginHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Get paginated and filtered Audit Logs.
     */
    public function auditLogs(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->input('record_type'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Get paginated and filtered User Activity Logs.
     */
    public function activityLogs(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('activity')) {
            $query->where('activity', $request->input('activity'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Get login and failed attempts history.
     */
    public function loginHistory(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $query = UserLoginHistory::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate(20));
    }
}
