<?php

namespace App\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Enums\CommercialComplaintStatus;
use App\Enums\QualityCheckResult;
use App\Models\Commercial\CommercialComplaint;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Employee;
use App\Models\Hr\EmployeeSalesTarget;
use App\Models\Production\ProductionOperation;
use App\Models\Production\QualityCheck;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PerformanceKpiCalculationService
{
    /**
     * @return array<string, float>
     */
    public function calculate(Employee $employee, Carbon $periodStart, Carbon $periodEnd): array
    {
        $kpis = [
            'production_output' => $this->productionOutput($employee, $periodStart, $periodEnd),
            'sales_actual' => $this->salesActual($employee, $periodStart, $periodEnd),
            'sales_target' => $this->salesTarget($employee, $periodStart, $periodEnd),
            'attendance_percent' => $this->attendancePercent($employee, $periodStart, $periodEnd),
            'quality_percent' => $this->qualityPercent($employee, $periodStart, $periodEnd),
            'job_completion_percent' => $this->jobCompletionPercent($employee, $periodStart, $periodEnd),
            'customer_rating' => $this->customerRating($employee, $periodStart, $periodEnd),
        ];

        $kpis['composite_score'] = $this->compositeScore($kpis);

        return $kpis;
    }

    public function productionOutput(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('production_operations')) {
            return 0;
        }

        return (float) ProductionOperation::query()
            ->where('company_id', $employee->company_id)
            ->where('assigned_employee_id', $employee->id)
            ->whereNotNull('ended_at')
            ->whereBetween('ended_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();
    }

    public function salesActual(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('sales_orders')) {
            return 0;
        }

        $userId = $this->linkedUserId($employee);
        if (! $userId) {
            return 0;
        }

        return (float) SalesOrder::query()
            ->where('company_id', $employee->company_id)
            ->where('created_by', $userId)
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()])
            ->sum('total_amount');
    }

    public function salesTarget(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('employee_sales_targets')) {
            return 0;
        }

        return (float) EmployeeSalesTarget::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->where('period_start', '<=', $end->toDateString())
            ->where('period_end', '>=', $start->toDateString())
            ->sum('target_amount');
    }

    public function attendancePercent(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('attendance_records')) {
            return 0;
        }

        $workingDays = max(1, $this->workingDaysInPeriod($start, $end));
        $daysWorked = $employee->attendanceRecords()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', [
                AttendanceStatus::Present->value,
                AttendanceStatus::Late->value,
                AttendanceStatus::HalfDay->value,
            ])
            ->count();

        return round(min(100, ($daysWorked / $workingDays) * 100), 2);
    }

    public function qualityPercent(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('quality_checks')) {
            return 0;
        }

        $userId = $this->linkedUserId($employee);
        if (! $userId) {
            return 0;
        }

        $total = QualityCheck::query()
            ->where('company_id', $employee->company_id)
            ->where('checked_by', $userId)
            ->whereBetween('checked_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $passed = QualityCheck::query()
            ->where('company_id', $employee->company_id)
            ->where('checked_by', $userId)
            ->where('result', QualityCheckResult::Passed->value)
            ->whereBetween('checked_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();

        return round(($passed / $total) * 100, 2);
    }

    public function jobCompletionPercent(Employee $employee, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('production_operations')) {
            return 0;
        }

        $total = ProductionOperation::query()
            ->where('company_id', $employee->company_id)
            ->where('assigned_employee_id', $employee->id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('started_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->orWhereBetween('ended_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
            })
            ->count();

        if ($total === 0) {
            return 0;
        }

        $completed = ProductionOperation::query()
            ->where('company_id', $employee->company_id)
            ->where('assigned_employee_id', $employee->id)
            ->whereNotNull('ended_at')
            ->whereBetween('ended_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    public function customerRating(Employee $employee, Carbon $start, Carbon $end): float
    {
        $userId = $this->linkedUserId($employee);
        if (! $userId) {
            return 0;
        }

        $resolved = 0;
        $total = 0;

        if (Schema::hasTable('commercial_complaints')) {
            $complaints = CommercialComplaint::query()
                ->where('company_id', $employee->company_id)
                ->where('assigned_to', $userId)
                ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

            $total += (clone $complaints)->count();
            $resolved += (clone $complaints)
                ->whereIn('status', [
                    CommercialComplaintStatus::Resolved->value,
                    CommercialComplaintStatus::Closed->value,
                ])
                ->count();
        }

        if (Schema::hasTable('commercial_support_tickets')) {
            $tickets = CommercialSupportTicket::query()
                ->where('company_id', $employee->company_id)
                ->where('assigned_to', $userId)
                ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

            $total += (clone $tickets)->count();
            $resolved += (clone $tickets)->whereNotNull('resolved_at')->count();
        }

        if ($total === 0) {
            return 0;
        }

        return round(($resolved / $total) * 100, 2);
    }

    /**
     * @param  array<string, float>  $kpis
     */
    public function compositeScore(array $kpis): float
    {
        $salesScore = $kpis['sales_target'] > 0
            ? min(100, ($kpis['sales_actual'] / $kpis['sales_target']) * 100)
            : ($kpis['sales_actual'] > 0 ? 75 : 0);

        $productionScore = $kpis['production_output'] > 0
            ? min(100, 50 + min(50, $kpis['production_output'] * 2))
            : 0;

        $weighted = (
            $productionScore * 0.20
            + $salesScore * 0.20
            + $kpis['attendance_percent'] * 0.20
            + $kpis['quality_percent'] * 0.15
            + $kpis['job_completion_percent'] * 0.15
            + $kpis['customer_rating'] * 0.10
        );

        return round(min(100, $weighted), 2);
    }

    public function suggestRating(float $compositeScore): \App\Enums\PerformanceRating
    {
        return match (true) {
            $compositeScore >= 90 => \App\Enums\PerformanceRating::Excellent,
            $compositeScore >= 75 => \App\Enums\PerformanceRating::Good,
            $compositeScore >= 60 => \App\Enums\PerformanceRating::Average,
            $compositeScore >= 40 => \App\Enums\PerformanceRating::Poor,
            default => \App\Enums\PerformanceRating::Critical,
        };
    }

    protected function linkedUserId(Employee $employee): ?int
    {
        return User::query()->where('employee_id', $employee->id)->value('id');
    }

    protected function workingDaysInPeriod(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return $days;
    }
}
