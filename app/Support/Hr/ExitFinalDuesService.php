<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollDeduction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ExitFinalDuesService
{
    /**
     * @return array<string, float>
     */
    public function calculate(Employee $employee, Carbon $lastWorkingDate): array
    {
        $compensation = $this->activeCompensation($employee);
        $basicSalary = (float) ($compensation?->basic_salary ?? 0);
        $dailyRate = $basicSalary > 0 ? round($basicSalary / 22, 2) : 0;

        $leaveDays = $this->leaveBalanceDays($employee);
        $leaveAmount = round($leaveDays * $dailyRate, 2);
        $salaryBalance = $this->proratedSalaryBalance($basicSalary, $lastWorkingDate);
        $deductions = $this->outstandingDeductions($employee);
        $net = round(max(0, $leaveAmount + $salaryBalance - $deductions), 2);

        return [
            'leave_balance_days' => $leaveDays,
            'leave_balance_amount' => $leaveAmount,
            'salary_balance' => $salaryBalance,
            'deductions_total' => $deductions,
            'net_final_dues' => $net,
        ];
    }

    protected function leaveBalanceDays(Employee $employee): float
    {
        if (! Schema::hasTable('leave_balances')) {
            return 0;
        }

        $annualTypeId = LeaveType::query()
            ->where('company_id', $employee->company_id)
            ->where('code', 'ANNUAL')
            ->value('id');

        if (! $annualTypeId) {
            return round((float) LeaveBalance::query()
                ->where('employee_id', $employee->id)
                ->where('balance_year', now()->year)
                ->get()
                ->sum(fn (LeaveBalance $b) => max(0, $b->available())), 2);
        }

        $balance = LeaveBalance::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $annualTypeId)
            ->where('balance_year', now()->year)
            ->first();

        return round(max(0, $balance?->available() ?? 0), 2);
    }

    protected function proratedSalaryBalance(float $basicSalary, Carbon $lastWorkingDate): float
    {
        if ($basicSalary <= 0) {
            return 0;
        }

        $monthStart = $lastWorkingDate->copy()->startOfMonth();
        $workingDays = 0;
        $cursor = $monthStart->copy();

        while ($cursor->lte($lastWorkingDate)) {
            if (! $cursor->isWeekend()) {
                $workingDays++;
            }
            $cursor->addDay();
        }

        $dailyRate = $basicSalary / 22;

        return round($workingDays * $dailyRate, 2);
    }

    protected function outstandingDeductions(Employee $employee): float
    {
        if (! Schema::hasTable('payroll_deductions')) {
            return 0;
        }

        return round((float) PayrollDeduction::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->whereNotIn('category', ['paye', 'shif', 'nssf', 'housing_levy'])
            ->sum('amount'), 2);
    }

    protected function activeCompensation(Employee $employee): ?EmployeeCompensation
    {
        if (! Schema::hasTable('employee_compensations')) {
            return null;
        }

        return EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->orderByDesc('effective_from')
            ->first();
    }
}
