<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Http\Requests\StoreReportTemplateRequest;
use App\Http\Requests\StoreScheduledReportRequest;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportTemplate;
use App\Models\ScheduledReport;
use App\Models\FavoriteReport;
use App\Repositories\ReportRepositoryInterface;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    protected ReportRepositoryInterface $reportRepo;
    protected ReportService $reportService;

    public function __construct(
        ReportRepositoryInterface $reportRepo,
        ReportService $reportService
    ) {
        $this->reportRepo = $reportRepo;
        $this->reportService = $reportService;
    }

    /**
     * Reports Dashboard index.
     */
    public function index(Request $request)
    {
        if (Gate::denies('viewAny', ReportDefinition::class)) {
            abort(403);
        }

        $user = $request->user();
        $categories = $this->reportRepo->getCategoriesWithDefinitions();
        $favorites = $this->reportRepo->getFavoriteDefinitions($user->id);
        $savedReports = $this->reportRepo->getSavedReports($user->id);

        $recentExports = ReportExport::where('executed_by', $user->id)
            ->with('reportDefinition')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $activeSchedules = ScheduledReport::where('created_by', $user->id)
            ->with('reportDefinition')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'favorites' => $favorites,
                'saved_reports' => $savedReports,
                'recent_exports' => $recentExports,
                'active_schedules' => $activeSchedules,
            ]);
        }

        return view('reports.index', compact(
            'categories', 'favorites', 'savedReports', 'recentExports', 'activeSchedules'
        ));
    }

    /**
     * Show report definition.
     */
    public function show(Request $request, string $uuid)
    {
        $definition = $this->reportRepo->findDefinitionByUuid($uuid);
        if (!$definition) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if (Gate::denies('view', $definition)) {
            abort(403);
        }

        return response()->json([
            'report' => $definition,
            'filters' => $definition->filters,
            'templates' => $definition->templates,
        ]);
    }

    /**
     * Preview report data (synchronous, limited to 10 records).
     */
    public function preview(Request $request, string $uuid)
    {
        $definition = $this->reportRepo->findDefinitionByUuid($uuid);
        if (!$definition) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if (Gate::denies('view', $definition)) {
            abort(403);
        }

        $filters = $request->input('filters', []);

        try {
            // Force pagination / limit to 10
            $data = $this->reportService->getReportData($definition->report_code, $filters, $request->user(), 10);
            return response()->json([
                'columns' => $definition->default_columns,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Queue/Trigger a full report export.
     */
    public function generate(GenerateReportRequest $request)
    {
        $validated = $request->validated();
        
        try {
            $export = $this->reportService->queueExport(
                $validated['report_code'],
                $validated['filters'] ?? [],
                $validated['export_format'],
                $request->user()
            );

            // Activity Log Entry
            activity()
                ->performedOn($export)
                ->causedBy($request->user())
                ->withProperties(['filters' => $validated['filters'] ?? [], 'format' => $validated['export_format']])
                ->log("Report Export Queued: {$validated['report_code']}");

            return response()->json([
                'message' => 'Report generation has been queued successfully.',
                'export' => $export,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Save customized report template.
     */
    public function saveTemplate(StoreReportTemplateRequest $request)
    {
        $validated = $request->validated();
        
        $template = ReportTemplate::create([
            'report_definition_id' => $validated['report_definition_id'],
            'template_name' => $validated['template_name'],
            'is_custom' => true,
            'custom_columns' => $validated['custom_columns'] ?? null,
            'custom_filters' => $validated['custom_filters'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        activity()
            ->performedOn($template)
            ->causedBy($request->user())
            ->log("Report Template Saved: {$validated['template_name']}");

        return response()->json([
            'message' => 'Template saved successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Create a scheduled report entry.
     */
    public function schedule(StoreScheduledReportRequest $request)
    {
        $validated = $request->validated();

        // Calculate next run date
        $now = now();
        $scheduleTime = $validated['schedule_time'];
        $nextRun = Carbon\Carbon::parse($scheduleTime);
        if ($nextRun->isPast()) {
            $nextRun->addDay();
        }

        $schedule = ScheduledReport::create([
            'report_definition_id' => $validated['report_definition_id'],
            'report_template_id' => $validated['report_template_id'] ?? null,
            'frequency' => $validated['frequency'],
            'schedule_time' => $scheduleTime,
            'recipient_email' => $validated['recipient_email'],
            'export_format' => $validated['export_format'],
            'next_run' => $nextRun,
            'created_by' => $request->user()->id,
        ]);

        activity()
            ->performedOn($schedule)
            ->causedBy($request->user())
            ->log("Report Scheduled: Definition ID {$validated['report_definition_id']}");

        return response()->json([
            'message' => 'Report scheduled successfully.',
            'schedule' => $schedule,
        ]);
    }

    /**
     * Toggle favorite report.
     */
    public function toggleFavorite(Request $request, string $uuid)
    {
        $definition = $this->reportRepo->findDefinitionByUuid($uuid);
        if (!$definition) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $user = $request->user();
        $fav = FavoriteReport::where('user_id', $user->id)
            ->where('report_definition_id', $definition->id)
            ->first();

        if ($fav) {
            $fav->delete();
            $status = false;
        } else {
            FavoriteReport::create([
                'user_id' => $user->id,
                'report_definition_id' => $definition->id,
            ]);
            $status = true;
        }

        return response()->json([
            'status' => $status,
            'message' => $status ? 'Report added to favorites.' : 'Report removed from favorites.',
        ]);
    }

    /**
     * Download completed export file.
     */
    public function download(Request $request, string $exportUuid)
    {
        $export = $this->reportRepo->findExportByUuid($exportUuid);
        if (!$export) {
            return response()->json(['message' => 'Export record not found'], 404);
        }

        // Authenticate download owner/RBAC
        if ($export->executed_by !== $request->user()->id && !$request->user()->hasRole('Admin')) {
            abort(403);
        }

        if ($export->status !== 'completed' || !$export->file_path) {
            return response()->json(['message' => 'File is not ready or failed to generate'], 400);
        }

        if (!Storage::disk('public')->exists($export->file_path)) {
            return response()->json(['message' => 'File does not exist on disk'], 404);
        }

        return Storage::disk('public')->download($export->file_path);
    }
}
