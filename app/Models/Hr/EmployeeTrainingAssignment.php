<?php

namespace App\Models\Hr;

use App\Enums\TrainingAssignmentStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'employee_id',
    'training_program_id',
    'reference',
    'status',
    'due_date',
    'hours_completed',
    'certificate_reference',
    'certificate_expires_at',
    'assigned_at',
    'completed_at',
    'assigned_by_user_id',
    'completed_by_user_id',
    'notes',
])]
class EmployeeTrainingAssignment extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'status' => TrainingAssignmentStatus::class,
            'due_date' => 'date',
            'hours_completed' => 'decimal:2',
            'certificate_expires_at' => 'date',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class, 'source_training_assignment_id');
    }

    public function isCertificateExpired(): bool
    {
        return $this->certificate_expires_at !== null && $this->certificate_expires_at->isPast();
    }

    public function isCertificateExpiringSoon(int $days = 30): bool
    {
        if ($this->certificate_expires_at === null || $this->isCertificateExpired()) {
            return false;
        }

        return $this->certificate_expires_at->lte(now()->addDays($days));
    }

    public function scopeForTenant(Builder $query): Builder
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId() ?? auth()->user()?->company_id) {
            return $query->where('company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeExpiringCertificates(Builder $query, int $days = 30): Builder
    {
        return $query
            ->where('status', TrainingAssignmentStatus::Completed->value)
            ->whereNotNull('certificate_expires_at')
            ->where('certificate_expires_at', '>=', now()->toDateString())
            ->where('certificate_expires_at', '<=', now()->addDays($days)->toDateString());
    }
}
