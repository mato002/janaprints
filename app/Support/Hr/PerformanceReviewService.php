<?php

namespace App\Support\Hr;

use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewCycle;
use App\Enums\PerformanceReviewStatus;
use App\Models\Employee;
use App\Models\Hr\EmployeeSalesTarget;
use App\Models\Hr\PerformanceReview;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PerformanceReviewService
{
    public function __construct(
        protected PerformanceKpiCalculationService $kpiCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PerformanceReview::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'reviewedBy']);

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['cycle'])) {
            $query->where('cycle', $filters['cycle']);
        }

        if (! empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('period_end')->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'cycles' => PerformanceReviewCycle::cases(),
            'ratings' => PerformanceRating::cases(),
            'statuses' => PerformanceReviewStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = PerformanceReview::query()
            ->forTenant()
            ->where('company_id', $companyId);

        return [
            'reviews_this_year' => (clone $base)->whereYear('period_end', now()->year)->count(),
            'submitted' => (clone $base)->where('status', PerformanceReviewStatus::Submitted->value)->count(),
            'excellent_count' => (clone $base)->where('rating', PerformanceRating::Excellent->value)->count(),
            'average_score' => round((float) (clone $base)->avg('composite_score'), 1),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data, User $user, bool $submit = false): PerformanceReview
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->whereKey($data['employee_id'])
            ->firstOrFail();

        $periodStart = Carbon::parse($data['period_start']);
        $periodEnd = Carbon::parse($data['period_end']);
        $kpis = $this->kpiCalculator->calculate($employee, $periodStart, $periodEnd);

        $rating = isset($data['rating'])
            ? PerformanceRating::from($data['rating'])
            : $this->kpiCalculator->suggestRating($kpis['composite_score']);

        return PerformanceReview::query()->create([
            'company_id' => $companyId,
            'branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'reference' => $this->nextReference($companyId),
            'cycle' => $data['cycle'],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'rating' => $rating,
            'status' => $submit ? PerformanceReviewStatus::Submitted : PerformanceReviewStatus::Draft,
            ...collect($kpis)->only([
                'production_output', 'sales_actual', 'sales_target', 'attendance_percent',
                'quality_percent', 'job_completion_percent', 'customer_rating', 'composite_score',
            ])->all(),
            'strengths' => $data['strengths'] ?? null,
            'improvements' => $data['improvements'] ?? null,
            'manager_notes' => $data['manager_notes'] ?? null,
            'reviewed_by_user_id' => $submit ? $user->id : null,
            'reviewed_at' => $submit ? now() : null,
        ]);
    }

    public function submit(PerformanceReview $review, User $user): PerformanceReview
    {
        if ($review->status === PerformanceReviewStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => __('This review has already been submitted.'),
            ]);
        }

        $review->update([
            'status' => PerformanceReviewStatus::Submitted,
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        return $review->fresh(['employee', 'reviewedBy']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsertSalesTarget(int $companyId, array $data): EmployeeSalesTarget
    {
        return EmployeeSalesTarget::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'employee_id' => $data['employee_id'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
            ],
            ['target_amount' => $data['target_amount']],
        );
    }

    /**
     * @return array<string, float>
     */
    public function previewKpis(int $companyId, int $employeeId, string $periodStart, string $periodEnd): array
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->whereKey($employeeId)
            ->firstOrFail();

        return $this->kpiCalculator->calculate(
            $employee,
            Carbon::parse($periodStart),
            Carbon::parse($periodEnd),
        );
    }

    protected function nextReference(int $companyId): string
    {
        $year = now()->year;
        $count = PerformanceReview::query()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('APR-%s-%04d', $year, $count);
    }
}
