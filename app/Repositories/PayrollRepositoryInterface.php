<?php

namespace App\Repositories;

use App\Models\EmployeeSalaryStructure;
use App\Models\Payslip;
use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PayrollRepositoryInterface extends BaseRepositoryInterface
{
    public function findActiveStructureForEmployee(int $employeeId): ?EmployeeSalaryStructure;
    
    public function getPayrollHistory(int $employeeId): Collection;
    
    public function getPayslipById(int $payslipId): ?Payslip;
    
    public function getPendingLoansForEmployee(int $employeeId): Collection;
    
    public function getActiveAdvancesForEmployee(int $employeeId, int $month, int $year): Collection;
    
    public function getPendingRevisionsForEmployee(int $employeeId): Collection;
    
    public function findRunByMonthAndYear(int $month, int $year): ?PayrollRun;
    
    public function paginateRuns(int $perPage = 15): LengthAwarePaginator;
}
