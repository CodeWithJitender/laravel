<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeSalaryStructure;
use App\Models\Payslip;
use App\Models\PayrollRun;
use App\Models\EmployeeLoan;
use App\Models\SalaryAdvance;
use App\Models\SalaryRevision;
use App\Repositories\PayrollRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPayrollRepository extends BaseRepository implements PayrollRepositoryInterface
{
    /**
     * EloquentPayrollRepository constructor.
     */
    public function __construct(PayrollRun $model)
    {
        parent::__construct($model);
    }

    /**
     * Find active structure for employee.
     */
    public function findActiveStructureForEmployee(int $employeeId): ?EmployeeSalaryStructure
    {
        return EmployeeSalaryStructure::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->first();
    }
    
    /**
     * Get payroll history for employee.
     */
    public function getPayrollHistory(int $employeeId): Collection
    {
        return Payslip::where('employee_id', $employeeId)
            ->whereNotNull('published_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get payslip by ID with details.
     */
    public function getPayslipById(int $payslipId): ?Payslip
    {
        return Payslip::with([
            'employee.employeeDetail.location', 
            'employee.employeeDetail.department', 
            'employee.employeeDetail.designation', 
            'payrollRunEmployee.items'
        ])->find($payslipId);
    }
    
    /**
     * Get active loans for employee.
     */
    public function getPendingLoansForEmployee(int $employeeId): Collection
    {
        return EmployeeLoan::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->get();
    }
    
    /**
     * Get active salary advances.
     */
    public function getActiveAdvancesForEmployee(int $employeeId, int $month, int $year): Collection
    {
        return SalaryAdvance::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('recovery_month', $month)
            ->where('recovery_year', $year)
            ->get();
    }
    
    /**
     * Get pending salary revisions.
     */
    public function getPendingRevisionsForEmployee(int $employeeId): Collection
    {
        return SalaryRevision::where('employee_id', $employeeId)
            ->whereNull('approved_by')
            ->get();
    }
    
    /**
     * Find payroll run by month and year.
     */
    public function findRunByMonthAndYear(int $month, int $year): ?PayrollRun
    {
        return $this->model->where('run_month', $month)
            ->where('run_year', $year)
            ->first();
    }
    
    /**
     * Paginate payroll runs.
     */
    public function paginateRuns(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('run_year', 'desc')
            ->orderBy('run_month', 'desc')
            ->paginate($perPage);
    }
}
