<?php

namespace App\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Enums\PayrollItemType;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollAllowance;
use App\Models\Hr\PayrollDeduction;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollPayslipItem;
use Illuminate\Support\Carbon;

class PayrollCalculationService
{
    /**
     * @return array<string, mixed>
     */
    public function calculateForEmployee(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): array {
        $compensation = $this->activeCompensation($employee);
        $attendance = $this->attendanceSummary($employee, $periodStart, $periodEnd);
        $workingDays = max(1, $this->workingDaysInPeriod($periodStart, $periodEnd));

        $basic = (float) ($compensation?->basic_salary ?? 0);
        $house = (float) ($compensation?->house_allowance ?? 0);
        $transport = (float) ($compensation?->transport_allowance ?? 0);
        $medical = (float) ($compensation?->medical_allowance ?? 0);

        $unpaidLeaveDays = $this->unpaidLeaveDays($employee, $periodStart, $periodEnd);
        $proration = max(0, ($workingDays - $unpaidLeaveDays) / $workingDays);
        $basic *= $proration;

        $customAllowances = PayrollAllowance::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();

        $customDeductions = PayrollDeduction::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->whereNotIn('category', ['paye', 'shif', 'nssf', 'housing_levy'])
            ->get();

        $totalCustomAllowances = $customAllowances->sum('amount');
        $gross = round($basic + $house + $transport + $medical + $totalCustomAllowances, 2);

        $nssf = $this->calculateNssf($gross);
        $shif = $this->calculateShif($gross);
        $housingLevy = $this->calculateHousingLevy($gross);
        $taxableIncome = max(0, $gross - $nssf);
        $paye = $this->calculatePaye($taxableIncome);
        $otherDeductions = round((float) $customDeductions->sum('amount'), 2);

        $totalDeductions = round($paye + $shif + $nssf + $housingLevy + $otherDeductions, 2);
        $net = round(max(0, $gross - $totalDeductions), 2);

        $items = [];
        $sort = 10;

        foreach ([
            ['BASIC', __('Basic Salary'), $basic],
            ['HOUSE', __('House Allowance'), $house],
            ['TRANSPORT', __('Transport Allowance'), $transport],
            ['MEDICAL', __('Medical Allowance'), $medical],
        ] as [$code, $name, $amount]) {
            if ($amount > 0) {
                $items[] = $this->item(PayrollItemType::Allowance, $code, $name, $amount, $sort);
                $sort += 10;
            }
        }

        foreach ($customAllowances as $allowance) {
            $items[] = $this->item(PayrollItemType::Allowance, $allowance->code, $allowance->name, (float) $allowance->amount, $sort);
            $sort += 10;
        }

        foreach ([
            ['PAYE', __('PAYE'), $paye],
            ['SHIF', __('SHIF'), $shif],
            ['NSSF', __('NSSF'), $nssf],
            ['HOUSING', __('Housing Levy'), $housingLevy],
        ] as [$code, $name, $amount]) {
            if ($amount > 0) {
                $items[] = $this->item(PayrollItemType::Deduction, $code, $name, $amount, $sort);
                $sort += 10;
            }
        }

        foreach ($customDeductions as $deduction) {
            $items[] = $this->item(PayrollItemType::Deduction, $deduction->code, $deduction->name, (float) $deduction->amount, $sort);
            $sort += 10;
        }

        return [
            'basic_salary' => round($basic, 2),
            'total_allowances' => round($house + $transport + $medical + $totalCustomAllowances, 2),
            'gross_pay' => $gross,
            'paye' => $paye,
            'shif' => $shif,
            'nssf' => $nssf,
            'housing_levy' => $housingLevy,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_pay' => $net,
            'days_worked' => $attendance['days_worked'],
            'leave_days' => $attendance['leave_days'],
            'absent_days' => $attendance['absent_days'],
            'items' => $items,
        ];
    }

    public function calculateNssf(float $gross): float
    {
        $pensionable = min(max($gross, 0), 36000);

        return round($pensionable * 0.06, 2);
    }

    public function calculateShif(float $gross): float
    {
        return round(max(300, $gross * 0.0275), 2);
    }

    public function calculateHousingLevy(float $gross): float
    {
        return round($gross * 0.015, 2);
    }

    public function calculatePaye(float $taxableIncome): float
    {
        $relief = 2400;
        $tax = 0;
        $remaining = $taxableIncome;

        foreach ([
            [24000, 0.10],
            [8333, 0.25],
            [467667, 0.30],
            [PHP_FLOAT_MAX, 0.35],
        ] as [$limit, $rate]) {
            if ($remaining <= 0) {
                break;
            }
            $chunk = min($remaining, $limit);
            $tax += $chunk * $rate;
            $remaining -= $chunk;
        }

        return round(max(0, $tax - $relief), 2);
    }

    public function persistPayslipItems(PayrollPayslip $payslip, array $items): void
    {
        $payslip->items()->delete();

        foreach ($items as $item) {
            PayrollPayslipItem::query()->create([
                'payroll_payslip_id' => $payslip->id,
                ...$item,
            ]);
        }
    }

    protected function activeCompensation(Employee $employee): ?EmployeeCompensation
    {
        return EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * @return array{days_worked: int, leave_days: int, absent_days: int}
     */
    protected function attendanceSummary(Employee $employee, Carbon $start, Carbon $end): array
    {
        $records = $employee->attendanceRecords()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return [
            'days_worked' => $records->whereIn('status', [
                AttendanceStatus::Present->value,
                AttendanceStatus::Late->value,
                AttendanceStatus::HalfDay->value,
            ])->count(),
            'leave_days' => $records->where('status', AttendanceStatus::Leave->value)->count(),
            'absent_days' => $records->where('status', AttendanceStatus::Absent->value)->count(),
        ];
    }

    protected function unpaidLeaveDays(Employee $employee, Carbon $start, Carbon $end): int
    {
        $unpaidTypeId = LeaveType::query()
            ->where('company_id', $employee->company_id)
            ->where('code', 'UNPAID')
            ->value('id');

        if (! $unpaidTypeId) {
            return 0;
        }

        return (int) $employee->leaveRequests()
            ->where('leave_type_id', $unpaidTypeId)
            ->where('status', 'approved')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->sum('days_requested');
    }

    /**
     * @return array{item_type: PayrollItemType, code: string, name: string, amount: float, sort_order: int}
     */
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

    protected function item(PayrollItemType $type, string $code, string $name, float $amount, int $sort): array
    {
        return [
            'item_type' => $type,
            'code' => $code,
            'name' => $name,
            'amount' => round($amount, 2),
            'sort_order' => $sort,
        ];
    }
}
