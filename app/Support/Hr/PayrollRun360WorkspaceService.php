<?php

namespace App\Support\Hr;

use App\Enums\PayrollItemType;
use App\Enums\PayrollRunStatus;
use App\Models\ActivityLog;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class PayrollRun360WorkspaceService
{
    public function __construct(
        protected PayrollReviewService $review,
        protected PayrollRunService $payrollRuns,
        protected PayrollApprovalWorkflowService $approvalWorkflow,
        protected PayrollEmployeeScopeService $employeeScope,
        protected PayrollIntegrityValidationService $integrity,
        protected PayrollFrozenSnapshotService $frozenSnapshots,
        protected PayrollGroupService $payrollGroups,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(PayrollRun $run): array
    {
        $run->load([
            'payslips.employee.branch',
            'payslips.items',
            'branch',
            'processedBy',
            'reviewedBy',
            'submittedForApprovalBy',
            'approvedBy',
            'postedBy',
            'paidBy',
            'cancelledBy',
            'postedJournal',
        ]);

        $review = $this->review->review($run);
        $earnings = $this->aggregateItems($run, PayrollItemType::Allowance);
        $deductions = $this->aggregateCustomDeductions($run);
        $statutories = $this->statutorySummary($run);

        return [
            'run' => $run,
            'overview' => $this->overview($run),
            'scope' => $this->scopeTab($run),
            'employees' => $this->employeesTab($run),
            'earnings' => $earnings,
            'deductions' => $deductions,
            'statutories' => $statutories,
            'review' => $review,
            'approvals' => $this->approvalsTab($run, $review),
            'accounting' => $this->accountingTab($run),
            'payslips' => $run->payslips,
            'audit_trail' => $this->auditTrail($run),
            'quick_actions' => $this->quickActions($run),
            'tabs' => $this->tabs(),
        ];
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['id' => 'overview', 'label' => __('Overview')],
            ['id' => 'scope', 'label' => __('Scope')],
            ['id' => 'employees', 'label' => __('Employees')],
            ['id' => 'earnings', 'label' => __('Earnings')],
            ['id' => 'deductions', 'label' => __('Deductions')],
            ['id' => 'statutories', 'label' => __('Statutories')],
            ['id' => 'review', 'label' => __('Review')],
            ['id' => 'approvals', 'label' => __('Approvals')],
            ['id' => 'accounting', 'label' => __('Accounting')],
            ['id' => 'payslips', 'label' => __('Payslips')],
            ['id' => 'audit-trail', 'label' => __('Audit Trail')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function overview(PayrollRun $run): array
    {
        return [
            'reference' => $run->reference,
            'branch' => $run->branch?->name ?? __('All branches'),
            'payroll_group' => $run->payroll_group,
            'payroll_group_label' => $this->payrollGroups->label((int) $run->company_id, (string) $run->payroll_group),
            'period_start' => $run->period_start,
            'period_end' => $run->period_end,
            'pay_date' => $run->pay_date,
            'status' => $run->status,
            'employee_count' => $run->employee_count,
            'gross_total' => $run->gross_total,
            'deductions_total' => $run->deductions_total,
            'net_total' => $run->net_total,
            'approval_status' => $this->approvalStatusLabel($run),
            'posting_status' => $this->postingStatusLabel($run),
            'notes' => $run->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function scopeTab(PayrollRun $run): array
    {
        $certification = $run->scope_snapshot ?? $this->employeeScope->certify($run);
        $integrity = $this->integrity->validateBeforeGeneration($run);

        return [
            'certification' => $certification,
            'integrity' => $integrity,
            'frozen_snapshot' => $run->frozen_snapshot,
            'frozen_intact' => $this->frozenSnapshots->matches($run),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeesTab(PayrollRun $run): array
    {
        $warningsByEmployee = collect($run->generation_warnings ?? [])
            ->keyBy('employee_id');

        return [
            'lines' => $run->payslips->map(function (PayrollPayslip $payslip) use ($warningsByEmployee) {
                $warning = $warningsByEmployee->get($payslip->employee_id);

                return [
                    'payslip' => $payslip,
                    'employee' => $payslip->employee,
                    'breakdown' => $payslip->calculation_breakdown,
                    'has_warning' => $warning !== null,
                    'problems' => $warning['problems'] ?? [],
                ];
            }),
            'scoped_count' => $this->payrollRuns->scopedEmployees($run)->count(),
        ];
    }

    /**
     * @return list<array{code: string, name: string, amount: float, employee_count: int}>
     */
    protected function aggregateItems(PayrollRun $run, PayrollItemType $type): array
    {
        $grouped = [];

        foreach ($run->payslips as $payslip) {
            foreach ($payslip->items->where('item_type', $type->value) as $item) {
                $key = $item->code;

                if (! isset($grouped[$key])) {
                    $grouped[$key] = [
                        'code' => $item->code,
                        'name' => $item->name,
                        'amount' => 0.0,
                        'employee_count' => 0,
                    ];
                }

                $grouped[$key]['amount'] += (float) $item->amount;
                $grouped[$key]['employee_count']++;
            }
        }

        return collect($grouped)->sortBy('code')->values()->all();
    }

    /**
     * @return list<array{code: string, name: string, amount: float, employee_count: int}>
     */
    protected function aggregateCustomDeductions(PayrollRun $run): array
    {
        $statutoryCodes = ['PAYE', 'SHIF', 'NSSF', 'HOUSING'];
        $grouped = [];

        foreach ($run->payslips as $payslip) {
            foreach ($payslip->items->where('item_type', PayrollItemType::Deduction->value) as $item) {
                if (in_array($item->code, $statutoryCodes, true)) {
                    continue;
                }

                $key = $item->code;

                if (! isset($grouped[$key])) {
                    $grouped[$key] = [
                        'code' => $item->code,
                        'name' => $item->name,
                        'amount' => 0.0,
                        'employee_count' => 0,
                    ];
                }

                $grouped[$key]['amount'] += (float) $item->amount;
                $grouped[$key]['employee_count']++;
            }
        }

        return collect($grouped)->sortBy('code')->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function statutorySummary(PayrollRun $run): array
    {
        return [
            'paye' => (float) $run->paye_total,
            'shif' => (float) $run->shif_total,
            'nssf' => (float) $run->nssf_total,
            'housing_levy' => (float) $run->housing_levy_total,
            'total' => round(
                (float) $run->paye_total
                + (float) $run->shif_total
                + (float) $run->nssf_total
                + (float) $run->housing_levy_total,
                2
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function approvalsTab(PayrollRun $run, array $review): array
    {
        $chainRun = app(\App\Support\Governance\ApprovalEnforcementEngine::class)->latestRun($run);

        return [
            'status' => $run->status,
            'chain_run' => $chainRun,
            'can_submit_for_approval' => $review['can_submit_for_approval'] ?? false,
            'timeline' => collect([
                ['label' => __('Generated'), 'user' => $run->processedBy?->name, 'at' => $run->processed_at, 'done' => $run->processed_at !== null],
                ['label' => __('Under review'), 'user' => $run->reviewedBy?->name, 'at' => $run->reviewed_at, 'done' => $run->reviewed_at !== null],
                ['label' => __('Pending approval'), 'user' => $run->submittedForApprovalBy?->name, 'at' => $run->submitted_for_approval_at, 'done' => $run->submitted_for_approval_at !== null],
                ['label' => __('Approved'), 'user' => $run->approvedBy?->name, 'at' => $run->approved_at, 'done' => $run->approved_at !== null],
                ['label' => __('Posted'), 'user' => $run->postedBy?->name, 'at' => $run->posted_at, 'done' => $run->posted_at !== null],
                ['label' => __('Paid'), 'user' => $run->paidBy?->name, 'at' => $run->paid_at, 'done' => $run->paid_at !== null],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function accountingTab(PayrollRun $run): array
    {
        return [
            'posted' => $run->postedJournal !== null,
            'journal' => $run->postedJournal,
            'posted_at' => $run->posted_at,
            'posted_by' => $run->postedBy?->name,
            'gross_total' => $run->gross_total,
            'net_total' => $run->net_total,
            'paye_total' => $run->paye_total,
            'shif_total' => $run->shif_total,
            'nssf_total' => $run->nssf_total,
            'housing_levy_total' => $run->housing_levy_total,
            'employer_nssf_total' => $run->employer_nssf_total,
            'employer_shif_total' => $run->employer_shif_total,
            'employer_housing_levy_total' => $run->employer_housing_levy_total,
        ];
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    protected function auditTrail(PayrollRun $run): Collection
    {
        $payslipIds = $run->payslips->pluck('id');

        return ActivityLog::query()
            ->forTenant()
            ->where(function ($query) use ($run, $payslipIds) {
                $query->where(function ($inner) use ($run) {
                    $inner->where('model_type', PayrollRun::class)
                        ->where('model_id', $run->id);
                });

                if ($payslipIds->isNotEmpty()) {
                    $query->orWhere(function ($inner) use ($payslipIds) {
                        $inner->where('model_type', PayrollPayslip::class)
                            ->whereIn('model_id', $payslipIds);
                    });
                }
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(PayrollRun $run): array
    {
        /** @var User|null $user */
        $user = auth()->user();
        $actions = [];

        if ($run->status->canGenerate() && $user && Gate::forUser($user)->allows('process', $run)) {
            $actions[] = [
                'type' => 'generate',
                'label' => $run->payslips()->exists() ? __('Regenerate payroll') : __('Generate payroll'),
                'route' => 'admin.hr.payroll.generate',
                'needs_confirm' => $run->payslips()->exists(),
            ];
        }

        if ($run->status->canSubmitReview() && $user && Gate::forUser($user)->allows('review', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Submit for review'),
                'route' => 'admin.hr.payroll.submit-review',
            ];
        }

        if ($run->status->canSubmitApproval() && $user && Gate::forUser($user)->allows('review', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Submit for approval'),
                'route' => 'admin.hr.payroll.submit-approval',
            ];
        }

        if ($run->status->canApprove() && $user && Gate::forUser($user)->allows('approve', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Approve'),
                'route' => 'admin.hr.payroll.approve',
            ];
            $actions[] = [
                'type' => 'post',
                'label' => __('Reject'),
                'route' => 'admin.hr.payroll.reject',
                'variant' => 'danger',
            ];
        }

        if ($run->status->canPost() && $user && Gate::forUser($user)->allows('post', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Post to accounting'),
                'route' => 'admin.hr.payroll.post',
            ];
        }

        if (in_array($run->status, [PayrollRunStatus::Posted, PayrollRunStatus::Paid], true)
            && $user && Gate::forUser($user)->allows('release', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Release payslips & email staff'),
                'route' => 'admin.hr.payroll.release-payslips',
                'needs_confirm' => true,
                'confirm_message' => __('Release payslips to employees and queue payslip emails?'),
            ];
        }

        if ($run->payslips()->exists() && in_array($run->status, [PayrollRunStatus::Posted, PayrollRunStatus::Paid], true)
            && $user && Gate::forUser($user)->allows('process', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Email all payslips'),
                'route' => 'admin.hr.payroll.email-payslips',
                'needs_confirm' => true,
                'confirm_message' => __('Queue payslip emails for all employees in this run?'),
            ];
        }

        if ($run->status->canMarkPaid() && $user && Gate::forUser($user)->allows('markPaid', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Mark as paid'),
                'route' => 'admin.hr.payroll.mark-paid',
            ];
        }

        if ($run->status->canCancel() && $user && Gate::forUser($user)->allows('process', $run)) {
            $actions[] = [
                'type' => 'post',
                'label' => __('Cancel run'),
                'route' => 'admin.hr.payroll.cancel',
                'variant' => 'danger',
            ];
        }

        return $actions;
    }

    protected function approvalStatusLabel(PayrollRun $run): string
    {
        return match ($run->status) {
            PayrollRunStatus::Approved,
            PayrollRunStatus::Posted,
            PayrollRunStatus::Paid => __('Approved'),
            PayrollRunStatus::PendingApproval => __('Pending approval'),
            PayrollRunStatus::UnderReview => __('Under review'),
            PayrollRunStatus::Generated => __('Not submitted'),
            PayrollRunStatus::Cancelled => __('Cancelled'),
            default => __('Not started'),
        };
    }

    protected function postingStatusLabel(PayrollRun $run): string
    {
        return match ($run->status) {
            PayrollRunStatus::Paid => __('Paid'),
            PayrollRunStatus::Posted => __('Posted'),
            PayrollRunStatus::Approved => __('Ready to post'),
            default => __('Not posted'),
        };
    }
}
