<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\ReportDefinition;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get central KPIs summary for dashboard widgets.
     */
    public function kpiSummary(Request $request)
    {
        if (Gate::denies('viewAnalytics', ReportDefinition::class)) {
            abort(403);
        }

        $kpis = $this->analyticsService->getKpis($request->user());

        return response()->json($kpis);
    }

    /**
     * Get monthly payroll costs trend chart data.
     */
    public function payrollTrend(Request $request)
    {
        if (Gate::denies('viewExecutiveReports', ReportDefinition::class)) {
            abort(403);
        }

        $months = $request->input('months', 6);
        $trends = $this->analyticsService->getPayrollCostTrend($request->user(), $months);

        return response()->json($trends);
    }

    /**
     * Get attendance status rate trends chart data.
     */
    public function attendanceTrend(Request $request)
    {
        if (Gate::denies('viewAnalytics', ReportDefinition::class)) {
            abort(403);
        }

        $days = $request->input('days', 7);
        $trends = $this->analyticsService->getAttendanceTrend($request->user(), $days);

        return response()->json($trends);
    }

    /**
     * Get headcount growth trends chart data.
     */
    public function headcountTrend(Request $request)
    {
        if (Gate::denies('viewExecutiveReports', ReportDefinition::class)) {
            abort(403);
        }

        $months = $request->input('months', 6);
        $trends = $this->analyticsService->getHeadcountGrowth($request->user(), $months);

        return response()->json($trends);
    }
}
