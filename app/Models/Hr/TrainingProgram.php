<?php

namespace App\Models\Hr;

use App\Enums\TrainingProgramStatus;
use App\Enums\TrainingType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'code',
    'type',
    'status',
    'title',
    'description',
    'duration_hours',
    'budget_amount',
    'scheduled_start_date',
    'scheduled_end_date',
    'requires_certification',
    'certificate_validity_days',
    'skill_tags',
    'evaluation_instructions',
    'is_active',
    'archived_at',
    'duplicated_from_id',
])]
class TrainingProgram extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'type' => TrainingType::class,
            'status' => TrainingProgramStatus::class,
            'duration_hours' => 'decimal:2',
            'budget_amount' => 'decimal:2',
            'scheduled_start_date' => 'date',
            'scheduled_end_date' => 'date',
            'requires_certification' => 'boolean',
            'certificate_validity_days' => 'integer',
            'skill_tags' => 'array',
            'evaluation_instructions' => 'string',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeTrainingAssignment::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TrainingEvaluation::class);
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicated_from_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(self::class, 'duplicated_from_id');
    }

    public function isAssignable(): bool
    {
        if ($this->status instanceof TrainingProgramStatus) {
            if (in_array($this->status, [
                TrainingProgramStatus::Archived,
                TrainingProgramStatus::Completed,
                TrainingProgramStatus::Cancelled,
            ], true)) {
                return false;
            }

            if ($this->status->isAssignable()) {
                return true;
            }
        }

        return (bool) $this->is_active;
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TrainingProgramStatus::Active->value);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', TrainingProgramStatus::Draft->value);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', TrainingProgramStatus::Archived->value);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->whereNotNull('scheduled_start_date')
            ->whereIn('status', [
                TrainingProgramStatus::Active->value,
                TrainingProgramStatus::Draft->value,
            ]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TrainingProgramStatus::Completed->value);
    }

    public function scopeAssignable(Builder $query): Builder
    {
        return $query->where('status', TrainingProgramStatus::Active->value);
    }
}
