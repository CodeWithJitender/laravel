<?php

namespace App\Repositories\Eloquent;

use App\Models\ReportCategory;
use App\Models\ReportDefinition;
use App\Models\SavedReport;
use App\Models\FavoriteReport;
use App\Models\ScheduledReport;
use App\Models\ReportExport;
use App\Repositories\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    public function __construct(ReportDefinition $model)
    {
        parent::__construct($model);
    }

    public function getCategoriesWithDefinitions(): Collection
    {
        return ReportCategory::with(['definitions' => function ($query) {
            $query->where('status', 'active');
        }])->get();
    }

    public function findDefinitionByCode(string $code): ?ReportDefinition
    {
        return $this->model->where('report_code', $code)
            ->where('status', 'active')
            ->with(['filters' => function ($query) {
                $query->where('status', 'active');
            }])
            ->first();
    }

    public function findDefinitionByUuid(string $uuid): ?ReportDefinition
    {
        return $this->model->where('uuid', $uuid)
            ->where('status', 'active')
            ->with(['filters' => function ($query) {
                $query->where('status', 'active');
            }])
            ->first();
    }

    public function getSavedReports(int $userId): Collection
    {
        return SavedReport::where('created_by', $userId)
            ->with('reportDefinition')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFavoriteDefinitions(int $userId): Collection
    {
        return $this->model->whereHas('favorites', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('status', 'active')->get();
    }

    public function getActiveScheduledReports(): Collection
    {
        return ScheduledReport::where('status', 'active')
            ->where('next_run', '<=', now())
            ->with(['reportDefinition', 'template'])
            ->get();
    }

    public function findExportByUuid(string $uuid): ?ReportExport
    {
        return ReportExport::where('uuid', $uuid)->first();
    }
}
