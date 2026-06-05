<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveType;
use Illuminate\Support\Carbon;

class LeaveBalanceService
{
    public function __construct(
        protected LeaveTypeService $leaveTypes,
    ) {}

    public function balanceFor(Employee $employee, LeaveType $leaveType, ?int $year = null): LeaveBalance
    {
        $year ??= (int) now()->year;

        return LeaveBalance::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'balance_year' => $year,
            ],
            [
                'company_id' => $employee->company_id,
                'opening_balance' => $leaveType->default_days_per_year ?? 0,
                'earned' => $this->earnedToDate($leaveType, $year),
                'taken' => 0,
                'pending' => 0,
            ],
        );
    }

    public function earnedToDate(LeaveType $leaveType, int $year): float
    {
        if ($leaveType->accrual_days_per_month === null) {
            return 0.0;
        }

        $months = $year === (int) now()->year
            ? now()->month
            : 12;

        return round((float) $leaveType->accrual_days_per_month * $months, 1);
    }

    public function addPending(LeaveBalance $balance, float $days): void
    {
        $balance->increment('pending', $days);
    }

    public function releasePending(LeaveBalance $balance, float $days): void
    {
        $balance->decrement('pending', min((float) $balance->pending, $days));
    }

    public function confirmTaken(LeaveBalance $balance, float $days): void
    {
        $balance->decrement('pending', min((float) $balance->pending, $days));
        $balance->increment('taken', $days);
    }

    public function assertSufficientBalance(LeaveBalance $balance, float $days): void
    {
        if ($balance->available() < $days && $balance->leaveType?->default_days_per_year !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'days_requested' => __('Insufficient leave balance. Available: :days days.', [
                    'days' => $balance->available(),
                ]),
            ]);
        }
    }

    /**
     * @return array<string, float>
     */
    public function summary(LeaveBalance $balance): array
    {
        return [
            'opening_balance' => (float) $balance->opening_balance,
            'earned' => (float) $balance->earned,
            'taken' => (float) $balance->taken,
            'pending' => (float) $balance->pending,
            'available' => $balance->available(),
        ];
    }

    public function initializeForEmployee(Employee $employee, ?int $year = null): void
    {
        $year ??= (int) now()->year;

        foreach ($this->leaveTypes->forCompany($employee->company_id) as $leaveType) {
            $this->balanceFor($employee, $leaveType, $year);
        }
    }
}
