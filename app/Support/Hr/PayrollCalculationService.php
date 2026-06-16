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
    public function __construct(
        protected PayrollStatutorySettingsService $statutorySettings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function calculateForEmployee(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): array {
        $companyId = (int) $employee->company_id;
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
            ->whereNotIn('category', ['paye', 'shif', 'nssf', 'housing_levy', 'nhif'])
            ->get();

        $totalCustomAllowances = $customAllowances->sum('amount');
        $gross = round($basic + $house + $transport + $medical + $totalCustomAllowances, 2);

        $nssf = $this->calculateNssf($gross, $companyId);
        $shif = $this->calculateShif($gross, $companyId);
        $housingLevy = $this->calculateHousingLevy($gross, $companyId);
        $taxableIncome = max(0, $gross - $nssf);
        $paye = $this->calculatePaye($taxableIncome, $companyId);

        $employerNssf = $this->calculateEmployerNssf($gross, $companyId);
        $employerShif = $this->calculateEmployerShif($gross, $companyId);
        $employerHousing = $this->calculateEmployerHousingLevy($gross, $companyId);

        $additionalStatutories = $this->calculateAdditionalStatutories($gross, $taxableIncome, $companyId);
        $additionalEmployeeTotal = round(collect($additionalStatutories)->where('side', 'employee')->sum('amount'), 2);

        $otherDeductions = round((float) $customDeductions->sum('amount'), 2);

        $totalDeductions = round($paye + $shif + $nssf + $housingLevy + $additionalEmployeeTotal + $otherDeductions, 2);
        $net = round($gross - $totalDeductions, 2);

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
            ['SHIF', __('SHIF/NHIF'), $shif],
            ['NSSF', __('NSSF'), $nssf],
            ['HOUSING', __('Housing Levy'), $housingLevy],
        ] as [$code, $name, $amount]) {
            if ($amount > 0) {
                $items[] = $this->item(PayrollItemType::Deduction, $code, $name, $amount, $sort);
                $sort += 10;
            }
        }

        foreach ($additionalStatutories as $statutory) {
            if ($statutory['side'] === 'employee' && $statutory['amount'] > 0) {
                $items[] = $this->item(
                    PayrollItemType::Deduction,
                    $statutory['code'],
                    $statutory['name'],
                    $statutory['amount'],
                    $sort,
                );
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
            'other_deductions' => $otherDeductions + $additionalEmployeeTotal,
            'total_deductions' => $totalDeductions,
            'net_pay' => $net,
            'employer_nssf' => $employerNssf,
            'employer_shif' => $employerShif,
            'employer_housing_levy' => $employerHousing,
            'days_worked' => $attendance['days_worked'],
            'leave_days' => $attendance['leave_days'],
            'absent_days' => $attendance['absent_days'],
            'items' => $items,
        ];
    }

    public function calculateNssf(float $gross, ?int $companyId = null): float
    {
        $pensionable = min(max($gross, 0), $this->statutorySettings->nssfPensionableCeiling($companyId));

        return round($pensionable * $this->statutorySettings->nssfEmployeeRate($companyId), 2);
    }

    public function calculateEmployerNssf(float $gross, ?int $companyId = null): float
    {
        $pensionable = min(max($gross, 0), $this->statutorySettings->nssfPensionableCeiling($companyId));

        return round($pensionable * $this->statutorySettings->nssfEmployerRate($companyId), 2);
    }

    public function calculateShif(float $gross, ?int $companyId = null): float
    {
        $rate = $this->statutorySettings->shifEmployeeRate($companyId);
        $minimum = $this->statutorySettings->shifMinimum($companyId);

        return round(max($minimum, $gross * $rate), 2);
    }

    public function calculateEmployerShif(float $gross, ?int $companyId = null): float
    {
        $rate = $this->statutorySettings->shifEmployerRate($companyId);

        if ($rate <= 0) {
            return 0.0;
        }

        return round($gross * $rate, 2);
    }

    public function calculateHousingLevy(float $gross, ?int $companyId = null): float
    {
        return round($gross * $this->statutorySettings->housingEmployeeRate($companyId), 2);
    }

    public function calculateEmployerHousingLevy(float $gross, ?int $companyId = null): float
    {
        return round($gross * $this->statutorySettings->housingEmployerRate($companyId), 2);
    }

    public function calculatePaye(float $taxableIncome, ?int $companyId = null): float
    {
        $relief = $this->statutorySettings->payePersonalRelief($companyId);
        $tax = 0;
        $remaining = $taxableIncome;

        foreach ($this->statutorySettings->payeBands($companyId) as $band) {
            if ($remaining <= 0) {
                break;
            }
            $limit = (float) ($band['limit'] ?? 0);
            $rate = (float) ($band['rate'] ?? 0);
            $chunk = min($remaining, $limit);
            $tax += $chunk * $rate;
            $remaining -= $chunk;
        }

        return round(max(0, $tax - $relief), 2);
    }

    /**
     * @return list<array{code: string, name: string, amount: float, side: string}>
     */
    protected function calculateAdditionalStatutories(float $gross, float $taxableIncome, int $companyId): array
    {
        $results = [];

        foreach ($this->statutorySettings->additionalStatutories($companyId) as $entry) {
            $base = ($entry['base'] ?? 'gross') === 'taxable' ? $taxableIncome : $gross;
            $rate = (float) ($entry['rate'] ?? 0);
            $amount = round(max(0, $base) * $rate, 2);

            if ($amount <= 0) {
                continue;
            }

            $results[] = [
                'code' => (string) ($entry['code'] ?? 'STAT'),
                'name' => (string) ($entry['name'] ?? __('Statutory Deduction')),
                'amount' => $amount,
                'side' => (string) ($entry['side'] ?? 'employee'),
            ];
        }

        return $results;
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
