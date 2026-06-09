<?php

namespace App\Jobs;

use App\Models\ReportExport;
use App\Models\ReportExecutionLog;
use App\Services\ReportService;
use App\Services\Export\ExportServiceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    /**
     * Execute the job.
     */
    public function handle(ReportService $reportService, ExportServiceManager $exportManager)
    {
        $export = ReportExport::find($this->exportId);
        if (!$export || $export->status !== 'pending') {
            return;
        }

        $export->update(['status' => 'processing']);
        $startTime = microtime(true);

        try {
            $definition = $export->reportDefinition;
            $user = $export->executor;
            $filters = $export->parameters ?? [];
            $format = $export->export_format;

            // Resolve custom columns if custom template, otherwise default
            $columns = $definition->default_columns;

            // Fetch data
            $data = $reportService->getReportData($definition->report_code, $filters, $user);

            // Export using format driver
            $filePath = $exportManager->export($definition->report_name, $columns, $data, $format);

            $fileSize = Storage::disk('public')->exists($filePath) ? Storage::disk('public')->size($filePath) : 0;

            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ]);

            $executionTime = (int) (round(microtime(true) - $startTime) * 1000);

            // Save log
            ReportExecutionLog::create([
                'report_definition_id' => $definition->id,
                'executed_by' => $user->id,
                'execution_time' => $executionTime,
                'status' => 'success',
                'file_path' => $filePath,
                'parameters' => $filters,
            ]);

            event(new \App\Events\ReportGenerated($export));

        } catch (Exception $e) {
            $executionTime = (int) (round(microtime(true) - $startTime) * 1000);

            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            ReportExecutionLog::create([
                'report_definition_id' => $export->report_definition_id,
                'executed_by' => $export->executed_by,
                'execution_time' => $executionTime,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'parameters' => $export->parameters,
            ]);

            event(new \App\Events\ReportGenerationFailed($export));

            throw $e;
        }
    }
}
