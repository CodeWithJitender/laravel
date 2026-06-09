<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditModelObserver
{
    protected AuditService $auditService;

    // Ignore sensitive or trivial columns from audit tracking
    protected array $ignoredColumns = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
    ];

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function created(Model $model)
    {
        $newValues = array_diff_key($model->getAttributes(), array_flip($this->ignoredColumns));
        $module = $this->resolveModule($model);

        $this->auditService->logChange(
            Auth::user(),
            $module,
            'create',
            $model,
            null,
            $newValues
        );
    }

    public function updated(Model $model)
    {
        $dirty = $model->getDirty();
        $oldValues = [];
        $newValues = [];

        foreach ($dirty as $key => $newValue) {
            if (in_array($key, $this->ignoredColumns)) {
                continue;
            }

            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        if (empty($newValues)) {
            return; // No auditable columns changed
        }

        $module = $this->resolveModule($model);

        $this->auditService->logChange(
            Auth::user(),
            $module,
            'update',
            $model,
            $oldValues,
            $newValues
        );
    }

    public function deleted(Model $model)
    {
        $oldValues = array_diff_key($model->getAttributes(), array_flip($this->ignoredColumns));
        $module = $this->resolveModule($model);

        $this->auditService->logChange(
            Auth::user(),
            $module,
            'delete',
            $model,
            $oldValues,
            null
        );
    }

    /**
     * Resolve the module name from the model class.
     */
    protected function resolveModule(Model $model): string
    {
        $class = class_basename($model);
        
        switch ($class) {
            case 'User':
            case 'EmployeeDetail':
                return 'Employee Management';
            case 'Department':
            case 'Designation':
            case 'Location':
            case 'Shift':
                return 'Organization Setup';
            case 'Attendance':
            case 'AttendanceCorrection':
                return 'Attendance Management';
            case 'LeaveRequest':
            case 'LeavePolicy':
            case 'LeaveType':
                return 'Leave Management';
            case 'PayrollRun':
            case 'Payslip':
            case 'EmployeeLoan':
            case 'SalaryAdvance':
                return 'Payroll Management';
            default:
                return 'Platform Governance';
        }
    }
}
