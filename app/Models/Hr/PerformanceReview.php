<?php

namespace App\Models\Hr;

use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewCycle;
use App\Enums\PerformanceReviewStatus;
use App\Models\Branch;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'branch_id',
    'employee_id',
    'reference',
    'cycle',
    'period_start',
    'period_end',
    'rating',
    'status',
    'production_output',
    'sales_actual',
    'sales_target',
    'attendance_percent',
    'quality_percent',
    'job_completion_percent',
    'customer_rating',
    'composite_score',
    'strengths',
    'improvements',
    'manager_notes',
    'reviewed_by_user_id',
    'reviewed_at',
])]
class PerformanceReview extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'cycle' => PerformanceReviewCycle::class,
            'rating' => PerformanceRating::class,
            'status' => PerformanceReviewStatus::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'production_output' => 'decimal:2',
            'sales_actual' => 'decimal:2',
            'sales_target' => 'decimal:2',
            'attendance_percent' => 'decimal:2',
            'quality_percent' => 'decimal:2',
            'job_completion_percent' => 'decimal:2',
            'customer_rating' => 'decimal:2',
            'composite_score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
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
