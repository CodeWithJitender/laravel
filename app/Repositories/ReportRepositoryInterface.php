<?php

namespace App\Repositories;

use App\Models\ReportCategory;
use App\Models\ReportDefinition;
use App\Models\SavedReport;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ReportRepositoryInterface extends BaseRepositoryInterface
{
    public function getCategoriesWithDefinitions(): Collection;

    public function findDefinitionByCode(string $code): ?ReportDefinition;

    public function findDefinitionByUuid(string $uuid): ?ReportDefinition;

    public function getSavedReports(int $userId): Collection;

    public function getFavoriteDefinitions(int $userId): Collection;

    public function getActiveScheduledReports(): Collection;

    public function findExportByUuid(string $uuid): ?ReportExport;
}
