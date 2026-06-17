<?php

namespace App\Support\Hr;

use App\Enums\DocumentType;
use App\Enums\PayrollRunStatus;
use App\Models\Employee;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Platform\NumberGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollRunService
{
    public function __construct(
        protected PayrollCalculationService $calculator,
        protected PayrollAccountingPostingService $accounting,
        protected NumberGenerator $numberGenerator,
        protected PayrollCompensationValidationService $validation,
        protected PayrollReviewService $review,
        protected PayrollApprovalWorkflowService $approvalWorkflow,
        protected PayrollAuditService $audit,
        protected EmployeeEmailService $employeeEmail,
        protected PayrollEmployeeScopeService $employeeScope,
        protected PayrollIntegrityValidationService $integrity,
        protected PayrollFrozenSnapshotService $frozenSnapshots,
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
        $reference = $this->numberGenerator->generate(
            DocumentType::PayrollRun,
            $companyId,
            isset($data['branch_id']) ? (int) $data['branch_id'] : null,
        );

        $run = PayrollRun::query()->create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'payroll_group' => $data['payroll_group'],
            'reference' => $reference,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'pay_date' => $data['pay_date'],
            'status' => PayrollRunStatus::Draft,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->audit->logRunCreated($run, $user);
        $this->audit->logPayrollGroupAssigned($run, $user);

        return $run;
    }

    public function generate(PayrollRun $run, User $user, bool $confirmRegenerate = false): PayrollRun
    {
        if ($run->status->isLockedForGeneration()) {
            throw ValidationException::withMessages([
                'status' => __('This payroll run cannot be generated in its current status.'),
            ]);
        }

        if (! $run->status->canGenerate()) {
            throw ValidationException::withMessages([
                'status' => __('This payroll run cannot be generated.'),
            ]);
        }

        $hasExistingLines = $run->payslips()->exists();

        if ($hasExistingLines && ! $confirmRegenerate) {
            throw ValidationException::withMessages([
                'confirm_regenerate' => __('Payroll lines already exist. Confirm regeneration to replace them.'),
            ]);
        }

        $integrityCheck = $this->integrity->validateBeforeGeneration($run);

        if (! $integrityCheck['valid']) {
            throw ValidationException::withMessages([
                'payroll_group' => collect($integrityCheck['warnings'])
                    ->pluck('message')
                    ->first() ?? __('Payroll cannot be generated for this run.'),
            ]);
        }

        return DB::transaction(function () use ($run, $user, $integrityCheck) {
            $run->payslips()->delete();

            $periodStart = Carbon::parse($run->period_start);
            $periodEnd = Carbon::parse($run->period_end);
            $employees = $this->scopedEmployees($run);

            $totals = [
                'gross' => 0, 'deductions' => 0, 'net' => 0,
                'paye' => 0, 'shif' => 0, 'nssf' => 0, 'housing' => 0,
                'employer_nssf' => 0, 'employer_shif' => 0, 'employer_housing' => 0,
            ];
            $warnings = [];

            foreach ($employees as $employee) {
                $problems = $this->validation->problemsForEmployee($employee);
                $calc = $this->calculator->calculateForEmployee($employee, $periodStart, $periodEnd);

                $payslip = PayrollPayslip::query()->create([
                    'company_id' => $run->company_id,
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'employee_compensation_id' => $calc['employee_compensation_id'] ?? null,
                    'reference' => $run->reference.'-'.$employee->employee_number,
                    ...collect($calc)->except([
                        'items',
                        'employer_nssf',
                        'employer_shif',
                        'employer_housing_levy',
                        'employee_compensation_id',
                    ])->all(),
                ]);

                if ($calc['items'] !== []) {
                    $this->calculator->persistPayslipItems($payslip, $calc['items']);
                }

                if ($problems !== [] || (float) $calc['gross_pay'] <= 0) {
                    $warnings[] = [
                        'employee_id' => $employee->id,
                        'employee_number' => $employee->employee_number,
                        'employee_name' => $employee->full_name,
                        'problems' => $problems !== []
                            ? $problems
                            : [__('No payable earnings for this period')],
                    ];
                }

                if ((float) $calc['gross_pay'] <= 0) {
                    continue;
                }

                $totals['gross'] += $calc['gross_pay'];
                $totals['deductions'] += $calc['total_deductions'];
                $totals['net'] += max(0, $calc['net_pay']);
                $totals['paye'] += $calc['paye'];
                $totals['shif'] += $calc['shif'];
                $totals['nssf'] += $calc['nssf'];
                $totals['housing'] += $calc['housing_levy'];
                $totals['employer_nssf'] += $calc['employer_nssf'];
                $totals['employer_shif'] += $calc['employer_shif'];
                $totals['employer_housing'] += $calc['employer_housing_levy'];
            }

            $review = $this->review->review($run->fresh(['payslips.employee']));
            $scopeSnapshot = $integrityCheck['scope'] ?? $this->employeeScope->certify($run);

            $run->update([
                'status' => PayrollRunStatus::Generated,
                'employee_count' => $run->payslips()->count(),
                'gross_total' => round($totals['gross'], 2),
                'deductions_total' => round($totals['deductions'], 2),
                'net_total' => round($totals['net'], 2),
                'paye_total' => round($totals['paye'], 2),
                'shif_total' => round($totals['shif'], 2),
                'nssf_total' => round($totals['nssf'], 2),
                'housing_levy_total' => round($totals['housing'], 2),
                'employer_nssf_total' => round($totals['employer_nssf'], 2),
                'employer_shif_total' => round($totals['employer_shif'], 2),
                'employer_housing_levy_total' => round($totals['employer_housing'], 2),
                'processed_by_user_id' => $user->id,
                'processed_at' => now(),
                'generation_warnings' => $warnings,
                'has_generation_warnings' => $warnings !== [],
                'review_snapshot' => $review,
                'scope_snapshot' => $scopeSnapshot,
                'frozen_snapshot' => null,
                'has_critical_review_issues' => ! $review['can_submit_for_approval'],
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
                'submitted_for_approval_by_user_id' => null,
                'submitted_for_approval_at' => null,
                'approved_by_user_id' => null,
                'approved_at' => null,
            ]);

            $run = $run->fresh(['payslips.employee', 'payslips.items']);
            $this->audit->logGenerated($run, $user);

            return $run;
        });
    }

    /** @deprecated Use generate() */
    public function calculate(PayrollRun $run, User $user): PayrollRun
    {
        return $this->generate($run, $user, $run->payslips()->exists());
    }

    public function submitForReview(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canSubmitReview()) {
            throw ValidationException::withMessages([
                'status' => __('Only generated payroll runs can be submitted for review.'),
            ]);
        }

        if (! $run->payslips()->exists()) {
            throw ValidationException::withMessages([
                'payslips' => __('Generate payroll lines before submitting for review.'),
            ]);
        }

        $review = $this->review->review($run);

        $run->update([
            'status' => PayrollRunStatus::UnderReview,
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
            'review_snapshot' => $review,
            'has_critical_review_issues' => ! $review['can_submit_for_approval'],
        ]);

        $this->audit->logReviewed($run->fresh(), $user, $review);

        return $run->fresh();
    }

    public function submitForApproval(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canSubmitApproval()) {
            throw ValidationException::withMessages([
                'status' => __('Only payroll runs under review can be submitted for approval.'),
            ]);
        }

        return $this->approvalWorkflow->submitForApproval($run, $user);
    }

    public function approve(PayrollRun $run, User $user): PayrollRun
    {
        return $this->approvalWorkflow->approve($run, $user);
    }

    public function reject(PayrollRun $run, User $user, ?string $reason = null): PayrollRun
    {
        return $this->approvalWorkflow->reject($run, $user, $reason);
    }

    public function post(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canPost()) {
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

            $run = $run->fresh(['postedJournal']);
            $this->audit->logPosted($run, $user);

            return $run;
        });
    }

    /**
     * @return array{run: PayrollRun, emails: array{queued: int, skipped: int}, released_count: int}
     */
    public function releasePayslips(PayrollRun $run, User $user): array
    {
        if (! in_array($run->status, [PayrollRunStatus::Posted, PayrollRunStatus::Paid], true)) {
            throw ValidationException::withMessages([
                'status' => __('Payslips can only be released after payroll is posted.'),
            ]);
        }

        $pending = $run->payslips()->with(['employee', 'payrollRun'])->whereNull('released_at')->get();
        $run->payslips()->whereNull('released_at')->update(['released_at' => now()]);

        foreach ($pending as $payslip) {
            $this->audit->logPayslipReleased($payslip->fresh(), $user);
        }

        $emails = ['queued' => 0, 'skipped' => 0];

        if (config('payroll.automation.email_payslips_on_release', true)) {
            foreach ($pending as $payslip) {
                if ($this->employeeEmail->sendPayslip($payslip->fresh(['employee', 'payrollRun']), $user)) {
                    $emails['queued']++;
                } else {
                    $emails['skipped']++;
                }
            }
        }

        return [
            'run' => $run->fresh(['payslips.employee']),
            'emails' => $emails,
            'released_count' => $pending->count(),
        ];
    }

    public function markPaid(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canMarkPaid()) {
            throw ValidationException::withMessages([
                'status' => __('Only posted payroll runs can be marked as paid.'),
            ]);
        }

        $run->update([
            'status' => PayrollRunStatus::Paid,
            'paid_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        return $run->fresh();
    }

    public function cancel(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canCancel()) {
            throw ValidationException::withMessages([
                'status' => __('This payroll run cannot be cancelled.'),
            ]);
        }

        $run->update([
            'status' => PayrollRunStatus::Cancelled,
            'cancelled_by_user_id' => $user->id,
            'cancelled_at' => now(),
        ]);

        return $run->fresh();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Employee>
     */
    public function scopedEmployees(PayrollRun $run)
    {
        return $this->employeeScope->includedEmployees($run);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFrozenSnapshot(PayrollRun $run): array
    {
        return $this->frozenSnapshots->build($run);
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = PayrollRun::query()->where('company_id', $companyId);

        return [
            'pending_approval' => (clone $base)->where('status', PayrollRunStatus::PendingApproval->value)->count(),
            'posted_this_year' => (clone $base)->where('status', PayrollRunStatus::Posted->value)->whereYear('posted_at', now()->year)->count(),
            'last_net_total' => (clone $base)->whereIn('status', [PayrollRunStatus::Posted->value, PayrollRunStatus::Paid->value])->orderByDesc('posted_at')->value('net_total') ?? 0,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PayrollRun>
     */
    public function recentRuns(int $companyId, int $limit = 5)
    {
        return PayrollRun::query()
            ->where('company_id', $companyId)
            ->latest('pay_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
