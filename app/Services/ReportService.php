<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\Holiday;
use App\Models\OptionalHolidayRequest;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportExecutionLog;
use App\Models\SavedReport;
use App\Repositories\ReportRepositoryInterface;
use App\Services\Export\ExportServiceManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportService
{
    protected ReportRepositoryInterface $reportRepo;

    public function __construct(ReportRepositoryInterface $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    /**
     * Build the query for a report code and apply filters.
     */
    public function buildQuery(string $reportCode, array $filters, User $user): Builder
    {
        $definition = $this->reportRepo->findDefinitionByCode($reportCode);
        if (!$definition) {
            throw new \Exception("Report definition not found for code: {$reportCode}");
        }

        $baseModelClass = $definition->query_builder_config['base_model'] ?? User::class;
        if (!class_exists($baseModelClass)) {
            throw new \Exception("Model class not found: {$baseModelClass}");
        }

        /** @var Builder $query */
        $query = $baseModelClass::query();

        // Load relationships specified in config
        if (!empty($definition->query_builder_config['with'])) {
            $query->with($definition->query_builder_config['with']);
        }

        // Apply dynamic filters
        $this->applyFilters($query, $reportCode, $filters, $baseModelClass);

        // Apply RBAC scopes
        $this->applyRbacScope($query, $user, $baseModelClass);

        return $query;
    }

    /**
     * Execute query and get data.
     */
    public function getReportData(string $reportCode, array $filters, User $user, int $perPage = 0)
    {
        $query = $this->buildQuery($reportCode, $filters, $user);

        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Apply filter parameters to the query.
     */
    protected function applyFilters(Builder $query, string $reportCode, array $filters, string $baseModelClass): void
    {
        // Resolve target fields based on base model
        $employeeRelation = 'employeeDetail';
        if ($baseModelClass === User::class) {
            $employeeRelation = 'employeeDetail';
        }

        // Apply Department filter
        if (!empty($filters['department_id'])) {
            if ($baseModelClass === User::class) {
                $query->whereHas('employeeDetail', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            } elseif (in_array($baseModelClass, [Attendance::class, LeaveRequest::class, Payslip::class])) {
                $userField = $baseModelClass === Attendance::class ? 'user_id' : 'employee_id';
                $query->whereHas('employee.' . $employeeRelation, function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            }
        }

        // Apply Location filter
        if (!empty($filters['location_id'])) {
            if ($baseModelClass === User::class) {
                $query->whereHas('employeeDetail', function ($q) use ($filters) {
                    $q->where('location_id', $filters['location_id']);
                });
            } elseif (in_array($baseModelClass, [Attendance::class, LeaveRequest::class, Payslip::class])) {
                $query->whereHas('employee.' . $employeeRelation, function ($q) use ($filters) {
                    $q->where('location_id', $filters['location_id']);
                });
            }
        }

        // Apply Designation filter
        if (!empty($filters['designation_id'])) {
            if ($baseModelClass === User::class) {
                $query->whereHas('employeeDetail', function ($q) use ($filters) {
                    $q->where('designation_id', $filters['designation_id']);
                });
            } elseif (in_array($baseModelClass, [Attendance::class, LeaveRequest::class, Payslip::class])) {
                $query->whereHas('employee.' . $employeeRelation, function ($q) use ($filters) {
                    $q->where('designation_id', $filters['designation_id']);
                });
            }
        }

        // Apply Manager filter
        if (!empty($filters['manager_id'])) {
            if ($baseModelClass === User::class) {
                $query->whereHas('employeeDetail', function ($q) use ($filters) {
                    $q->where('manager_id', $filters['manager_id']);
                });
            } elseif (in_array($baseModelClass, [Attendance::class, LeaveRequest::class, Payslip::class])) {
                $query->whereHas('employee.' . $employeeRelation, function ($q) use ($filters) {
                    $q->where('manager_id', $filters['manager_id']);
                });
            }
        }

        // Apply Date Range filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();

            if ($baseModelClass === Attendance::class) {
                $query->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()]);
            } elseif ($baseModelClass === LeaveRequest::class) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
                });
            } elseif ($baseModelClass === Payslip::class) {
                $query->whereHas('payrollRunEmployee.payrollRun', function ($q) use ($startDate, $endDate) {
                    // Approximate run month matching
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                });
            } elseif ($baseModelClass === User::class) {
                // If filtering joining dates for employee joining report
                if ($reportCode === 'EMP_JOIN') {
                    $query->whereHas('employeeDetail', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('joining_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
                } elseif ($reportCode === 'EMP_EXIT') {
                    $query->whereHas('employeeDetail', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('exit_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
                }
            }
        }

        // Apply Status filter
        if (!empty($filters['status'])) {
            if (Schema::hasColumn((new $baseModelClass)->getTable(), 'status')) {
                $query->where('status', $filters['status']);
            }
        }
    }

    /**
     * Enforce security boundary scopes:
     * - Admins see all.
     * - Managers see direct/indirect reports in their department/location.
     * - Employees see only their own.
     */
    protected function applyRbacScope(Builder $query, User $user, string $baseModelClass): void
    {
        if ($user->hasRole('Admin')) {
            return; // Admins have global access
        }

        $userId = $user->id;

        if ($user->hasRole('Manager')) {
            // Get manager details
            $managerDetail = $user->employeeDetail;
            $managerDeptId = $managerDetail ? $managerDetail->department_id : null;
            $managerLocId = $managerDetail ? $managerDetail->location_id : null;

            // Scope where user is direct manager, or user is in same department
            if ($baseModelClass === User::class) {
                $query->where(function ($q) use ($userId, $managerDeptId, $managerLocId) {
                    $q->whereHas('employeeDetail', function ($sq) use ($userId, $managerDeptId, $managerLocId) {
                        $sq->where('manager_id', $userId);
                        if ($managerDeptId) {
                            $sq->orWhere('department_id', $managerDeptId);
                        }
                    });
                });
            } elseif ($baseModelClass === Attendance::class) {
                $query->where(function ($q) use ($userId, $managerDeptId) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('employee.employeeDetail', function ($sq) use ($userId, $managerDeptId) {
                          $sq->where('manager_id', $userId);
                          if ($managerDeptId) {
                              $sq->orWhere('department_id', $managerDeptId);
                          }
                      });
                });
            } elseif (in_array($baseModelClass, [LeaveRequest::class, Payslip::class, OptionalHolidayRequest::class])) {
                $query->where(function ($q) use ($userId, $managerDeptId) {
                    $q->where('employee_id', $userId)
                      ->orWhereHas('employee.employeeDetail', function ($sq) use ($userId, $managerDeptId) {
                          $sq->where('manager_id', $userId);
                          if ($managerDeptId) {
                              $sq->orWhere('department_id', $managerDeptId);
                          }
                      });
                });
            }
            return;
        }

        // Standard Employee scope: self-service only
        if ($baseModelClass === User::class) {
            $query->where('id', $userId);
        } elseif ($baseModelClass === Attendance::class) {
            $query->where('user_id', $userId);
        } elseif (in_array($baseModelClass, [LeaveRequest::class, Payslip::class, OptionalHolidayRequest::class])) {
            $query->where('employee_id', $userId);
        } else {
            // Safe fallback: empty results if not specifically mapped
            $query->whereRaw('1 = 0');
        }
    }

    /**
     * Create an asynchronous report export job.
     */
    public function queueExport(string $reportCode, array $filters, string $format, User $user): ReportExport
    {
        $definition = $this->reportRepo->findDefinitionByCode($reportCode);
        if (!$definition) {
            throw new \Exception("Report definition not found: {$reportCode}");
        }

        $export = ReportExport::create([
            'report_definition_id' => $definition->id,
            'executed_by' => $user->id,
            'status' => 'pending',
            'export_format' => $format,
            'parameters' => $filters,
        ]);

        // Dispatch background processing job
        \App\Jobs\ExportReportJob::dispatch($export->id);

        return $export;
    }
}
