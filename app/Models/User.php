<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->password_changed_at)) {
                $model->password_changed_at = now();
            }
        });

        static::created(function ($model) {
            event(new \App\Events\EmployeeCreated($model));
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'uuid',
        'failed_login_attempts',
        'locked_at',
        'password_changed_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employeeDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmployeeDetail::class, 'user_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }

    public function loginHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoginHistory::class, 'user_id');
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function corrections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceCorrection::class, 'user_id');
    }

    public function attendanceSummaries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceMonthlySummary::class, 'user_id');
    }

    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    public function leaveAccruals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveAccrual::class, 'employee_id');
    }

    public function leaveCarryForwards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveCarryForward::class, 'employee_id');
    }

    public function notificationRecipients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificationRecipient::class, 'employee_id');
    }

    public function announcementRecipients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnnouncementRecipient::class, 'employee_id');
    }



    public function employeeSalaryStructures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class, 'employee_id');
    }

    public function salaryRevisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryRevision::class, 'employee_id');
    }

    public function payslips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    public function employeeLoans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeLoan::class, 'employee_id');
    }

    public function salaryAdvances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryAdvance::class, 'employee_id');
    }
}
