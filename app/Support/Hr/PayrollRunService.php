<?php

namespace App\Support\Hr;

use App\Enums\PayrollRunStatus;
use App\Models\Employee;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollRunService
{
    public function __construct(
        protected PayrollCalculationService $calculator,
        protected PayrollAccountingPostingService $accounting,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PayrollRun::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['branch', 'processedBy', 'approvedBy']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('pay_date')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data, User $user): PayrollRun
    {
        $reference = $this->nextReference($companyId);

        return PayrollRun::query()->create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'reference' => $reference,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'pay_date' => $data['pay_date'],
            'status' => PayrollRunStatus::Draft,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function calculate(PayrollRun $run, User $user): PayrollRun
    {
        if (! in_array($run->status, [PayrollRunStatus::Draft, PayrollRunStatus::Calculated], true)) {
            throw ValidationException::withMessages([
                'status' => __('This payroll run cannot be recalculated.'),
            ]);
        }

        return DB::transaction(function () use ($run, $user) {
            $run->payslips()->delete();

            $periodStart = Carbon::parse($run->period_start);
            $periodEnd = Carbon::parse($run->period_end);

            $employees = Employee::query()
                ->where('company_id', $run->company_id)
                ->where('is_active', true)
                ->when($run->branch_id, fn ($q) => $q->where('branch_id', $run->branch_id))
                ->get();

            $totals = [
                'gross' => 0, 'deductions' => 0, 'net' => 0,
                'paye' => 0, 'shif' => 0, 'nssf' => 0, 'housing' => 0,
            ];

            foreach ($employees as $employee) {
                $calc = $this->calculator->calculateForEmployee($employee, $periodStart, $periodEnd);

                if ($calc['gross_pay'] <= 0) {
                    continue;
                }

                $payslip = PayrollPayslip::query()->create([
                    'company_id' => $run->company_id,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'reference' => $run->reference.'-'.$employee->employee_number,
                    ...collect($calc)->except('items')->all(),
                ]);

                $this->calculator->persistPayslipItems($payslip, $calc['items']);

                $totals['gross'] += $calc['gross_pay'];
                $totals['deductions'] += $calc['total_deductions'];
                $totals['net'] += $calc['net_pay'];
                $totals['paye'] += $calc['paye'];
                $totals['shif'] += $calc['shif'];
                $totals['nssf'] += $calc['nssf'];
                $totals['housing'] += $calc['housing_levy'];
            }

            $run->update([
                'status' => PayrollRunStatus::Calculated,
                'employee_count' => $run->payslips()->count(),
                'gross_total' => round($totals['gross'], 2),
                'deductions_total' => round($totals['deductions'], 2),
                'net_total' => round($totals['net'], 2),
                'paye_total' => round($totals['paye'], 2),
                'shif_total' => round($totals['shif'], 2),
                'nssf_total' => round($totals['nssf'], 2),
                'housing_levy_total' => round($totals['housing'], 2),
                'processed_by_user_id' => $user->id,
                'processed_at' => now(),
            ]);

            return $run->fresh(['payslips.employee', 'payslips.items']);
        });
    }

    public function approve(PayrollRun $run, User $user): PayrollRun
    {
        if ($run->status !== PayrollRunStatus::Calculated) {
            throw ValidationException::withMessages([
                'status' => __('Only calculated payroll runs can be approved.'),
            ]);
        }

        $run->update([
            'status' => PayrollRunStatus::Approved,
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        return $run->fresh();
    }

    public function post(PayrollRun $run, User $user): PayrollRun
    {
        if ($run->status !== PayrollRunStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('Payroll must be approved before posting.'),
            ]);
        }

        return DB::transaction(function () use ($run, $user) {
            $journal = $this->accounting->postPayrollRun($run, $user->id);

            $run->update([
                'status' => PayrollRunStatus::Posted,
                'posted_journal_id' => $journal->id,
                'posted_by_user_id' => $user->id,
                'posted_at' => now(),
            ]);

            return $run->fresh(['postedJournal']);
        });
    }

    protected function nextReference(int $companyId): string
    {
        $year = now()->year;
        $count = PayrollRun::query()->where('company_id', $companyId)->whereYear('created_at', $year)->count() + 1;

        return sprintf('PR-%s-%04d', $year, $count);
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = PayrollRun::query()->where('company_id', $companyId);

        return [
            'pending_approval' => (clone $base)->where('status', PayrollRunStatus::Calculated->value)->count(),
            'posted_this_year' => (clone $base)->where('status', PayrollRunStatus::Posted->value)->whereYear('posted_at', now()->year)->count(),
            'last_net_total' => (clone $base)->where('status', PayrollRunStatus::Posted->value)->orderByDesc('posted_at')->value('net_total') ?? 0,
        ];
    }
}
