<?php

namespace App\Models\Hr;

use App\Enums\TrainingType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'type',
    'title',
    'description',
    'duration_hours',
    'requires_certification',
    'certificate_validity_days',
    'skill_tags',
    'is_active',
])]
class TrainingProgram extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'type' => TrainingType::class,
            'duration_hours' => 'decimal:2',
            'requires_certification' => 'boolean',
            'certificate_validity_days' => 'integer',
            'skill_tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeTrainingAssignment::class);
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
}
