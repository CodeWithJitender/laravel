<?php

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Models\ReportExecutionLog;
use App\Services\ReportService;
use App\Services\Export\ExportServiceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Exception;

class SendScheduledReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scheduledReportId;

    public function __construct(int $scheduledReportId)
    {
        $this->scheduledReportId = $scheduledReportId;
    }

    public function handle(ReportService $reportService, ExportServiceManager $exportManager)
    {
        $schedule = ScheduledReport::find($this->scheduledReportId);
        if (!$schedule || $schedule->status !== 'active') {
            return;
        }

        $startTime = microtime(true);

        try {
            $definition = $schedule->reportDefinition;
            $user = $schedule->creator ?? User::role('Admin')->first();
            $format = $schedule->export_format;
            $recipientEmails = array_map('trim', explode(',', $schedule->recipient_email));

            // Default filters (last month/week depending on frequency)
            $filters = $this->resolveFiltersForFrequency($schedule->frequency);

            $columns = $definition->default_columns;

            // Generate report
            $data = $reportService->getReportData($definition->report_code, $filters, $user);
            $filePath = $exportManager->export($definition->report_name, $columns, $data, $format);

            // Send Email with attachment
            $absolutePath = Storage::disk('public')->path($filePath);
            
            foreach ($recipientEmails as $email) {
                if (empty($email)) continue;
                
                Mail::raw("Please find attached the scheduled report: {$definition->report_name}.", function ($message) use ($email, $definition, $absolutePath, $filePath) {
                    $message->to($email)
                        ->subject("Scheduled Report: {$definition->report_name}")
                        ->attach($absolutePath, [
                            'as' => basename($filePath)
                        ]);
                });
            }

            $executionTime = (int) (round(microtime(true) - $startTime) * 1000);

            // Log execution
            ReportExecutionLog::create([
                'report_definition_id' => $definition->id,
                'executed_by' => $user->id ?? 1,
                'execution_time' => $executionTime,
                'status' => 'success',
                'file_path' => $filePath,
                'parameters' => $filters,
            ]);

            // Update next run
            $nextRun = $this->calculateNextRun($schedule->frequency);
            $schedule->update([
                'last_run' => now(),
                'next_run' => $nextRun,
            ]);

            event(new \App\Events\ReportGenerated(new ReportExport([
                'report_definition_id' => $definition->id,
                'executed_by' => $user->id ?? 1,
                'status' => 'completed',
                'export_format' => $format,
                'file_path' => $filePath,
            ])));

        } catch (Exception $e) {
            $executionTime = (int) (round(microtime(true) - $startTime) * 1000);

            ReportExecutionLog::create([
                'report_definition_id' => $schedule->report_definition_id,
                'executed_by' => $schedule->created_by ?? 1,
                'execution_time' => $executionTime,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'parameters' => [],
            ]);

            // Calculate next run anyway to prevent infinite loop locking
            $nextRun = $this->calculateNextRun($schedule->frequency);
            $schedule->update([
                'next_run' => $nextRun,
            ]);

            throw $e;
        }
    }

    protected function resolveFiltersForFrequency(string $frequency): array
    {
        $now = now();
        switch ($frequency) {
            case 'daily':
                return [
                    'start_date' => $now->copy()->subDay()->toDateString(),
                    'end_date' => $now->copy()->subDay()->toDateString(),
                ];
            case 'weekly':
                return [
                    'start_date' => $now->copy()->subWeek()->startOfWeek()->toDateString(),
                    'end_date' => $now->copy()->subWeek()->endOfWeek()->toDateString(),
                ];
            case 'monthly':
                return [
                    'start_date' => $now->copy()->subMonth()->startOfMonth()->toDateString(),
                    'end_date' => $now->copy()->subMonth()->endOfMonth()->toDateString(),
                ];
            case 'quarterly':
                return [
                    'start_date' => $now->copy()->subMonths(3)->startOfQuarter()->toDateString(),
                    'end_date' => $now->copy()->subMonths(1)->endOfQuarter()->toDateString(),
                ];
            case 'yearly':
                return [
                    'start_date' => $now->copy()->subYear()->startOfYear()->toDateString(),
                    'end_date' => $now->copy()->subYear()->endOfYear()->toDateString(),
                ];
            default:
                return [];
        }
    }

    protected function calculateNextRun(string $frequency): \Carbon\Carbon
    {
        $now = now();
        switch ($frequency) {
            case 'daily':
                return $now->addDay();
            case 'weekly':
                return $now->addWeek();
            case 'monthly':
                return $now->addMonth();
            case 'quarterly':
                return $now->addMonths(3);
            case 'yearly':
                return $now->addYear();
            default:
                return $now->addDay();
        }
    }
}
