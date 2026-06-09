<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalaryRevision;
use App\Models\EmployeeSalaryStructure;
use App\Models\SalaryStructure;
use App\Events\SalaryRevised;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryRevisionService
{
    /**
     * Propose a salary revision for an employee.
     */
    public function proposeRevision(
        int $employeeId,
        float $newGrossSalary,
        string $effectiveDate,
        string $reason = null
    ): SalaryRevision {
        $employee = User::findOrFail($employeeId);

        // Find active structure
        $activeStructure = EmployeeSalaryStructure::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->first();

        $oldGross = $activeStructure ? (float) $activeStructure->monthly_gross_salary : 0.00;
        $oldCtc = $activeStructure ? (float) $activeStructure->annual_ctc : 0.00;

        $newCtc = $newGrossSalary * 12;

        return SalaryRevision::create([
            'employee_id' => $employeeId,
            'old_gross_salary' => $oldGross,
            'new_gross_salary' => $newGrossSalary,
            'old_annual_ctc' => $oldCtc,
            'new_annual_ctc' => $newCtc,
            'revision_date' => now()->toDateString(),
            'effective_date' => $effectiveDate,
            'reason' => $reason,
            'approved_by' => null, // Pending approval
        ]);
    }

    /**
     * Approve a salary revision.
     */
    public function approveRevision(int $revisionId, int $approvedById, int $salaryStructureId = null): SalaryRevision
    {
        $revision = SalaryRevision::findOrFail($revisionId);

        if ($revision->approved_by) {
            throw new Exception("This salary revision has already been approved.");
        }

        return DB::transaction(function () use ($revision, $approvedById, $salaryStructureId) {
            // Update revision status
            $revision->update([
                'approved_by' => $approvedById,
            ]);

            // Deactivate any currently active structures for the employee
            $activeStructures = EmployeeSalaryStructure::where('employee_id', $revision->employee_id)
                ->where('status', 'active')
                ->get();

            $prevStructureId = null;
            foreach ($activeStructures as $active) {
                $prevStructureId = $active->salary_structure_id;
                // Set effective_to date as one day before the new revision's effective date
                $effectiveTo = Carbon::parse($revision->effective_date)->subDay()->toDateString();
                $active->update([
                    'status' => 'inactive',
                    'effective_to' => $effectiveTo,
                ]);
            }

            // If salaryStructureId was not passed, reuse the previous active structure or fall back to default
            if (!$salaryStructureId) {
                $salaryStructureId = $prevStructureId ?? SalaryStructure::where('status', 'active')->first()?->id;
            }

            if (!$salaryStructureId) {
                throw new Exception("No active Salary Structure found to assign to employee.");
            }

            // Create a new active structure assignment
            EmployeeSalaryStructure::create([
                'employee_id' => $revision->employee_id,
                'salary_structure_id' => $salaryStructureId,
                'effective_from' => $revision->effective_date,
                'effective_to' => null,
                'monthly_gross_salary' => $revision->new_gross_salary,
                'annual_ctc' => $revision->new_annual_ctc,
                'status' => 'active',
            ]);

            event(new SalaryRevised($revision));

            return $revision;
        });
    }
}
