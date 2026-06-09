<?php

namespace App\Support\Dashboard;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\Accounting\Journal;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PayrollRun;
use App\Models\Inventory\StockAdjustment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\SupplierPayment;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Accounting\JournalPostingService;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\EscalationEngine;
use App\Support\Governance\EscalationsService;
use App\Support\Hr\CompensationService;
use App\Support\Hr\LeaveRequestService;
use App\Support\Hr\PayrollRunService;
use App\Support\Procurement\PurchaseOrderService;
use App\Support\Procurement\PurchaseRequestService;
use App\Support\Procurement\SupplierPaymentService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\Sales\QuotationApprovalService;
use App\Support\StockAdjustmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class ExecutiveApprovalActionService
{
    public function __construct(
        protected QuotationApprovalService $quotationApprovals,
        protected LeaveRequestService $leaveRequests,
        protected CompensationService $compensation,
        protected PayrollRunService $payrollRuns,
        protected CustomerInvoiceService $customerInvoices,
        protected CustomerPaymentService $customerPayments,
        protected SupplierPaymentService $supplierPayments,
        protected JournalPostingService $journalPosting,
        protected ApprovalEnforcementEngine $approvalEngine,
        protected EscalationsService $escalations,
        protected EscalationEngine $escalationEngine,
    ) {}

    public function approve(User $actor, string $kind, int $subjectId, ?string $notes = null): void
    {
        $this->assertCanAct($actor, $kind, 'approve');

        match ($kind) {
            'quotation' => $this->quotationApprovals->approve(
                Quotation::query()->findOrFail($subjectId),
                $actor,
                $notes,
            ),
            'artwork' => $this->approveArtwork($actor, $subjectId, $notes),
            'purchase_request' => PurchaseRequestService::approve(
                PurchaseRequest::query()->findOrFail($subjectId),
            ),
            'purchase_order' => PurchaseOrderService::approve(
                PurchaseOrder::query()->findOrFail($subjectId),
                $actor,
                $notes,
            ),
            'leave' => $this->approveLeave($actor, $subjectId),
            'payroll' => $this->payrollRuns->approve(
                PayrollRun::query()->findOrFail($subjectId),
                $actor,
            ),
            'compensation' => $this->compensation->approve(
                EmployeeCompensation::query()->findOrFail($subjectId),
                $actor,
            ),
            'stock_adjustment' => StockAdjustmentService::approve(
                StockAdjustment::query()->findOrFail($subjectId),
                $actor->id,
                $notes,
            ),
            'credit_note' => $this->customerInvoices->approve(
                CustomerInvoice::query()->findOrFail($subjectId),
                $actor->id,
            ),
            'journal' => $this->postJournal($actor, $subjectId),
            'customer_payment' => $this->customerPayments->post(
                CustomerPayment::query()->findOrFail($subjectId),
                $actor->id,
            ),
            'supplier_payment' => $this->supplierPayments->post(
                SupplierPayment::query()->findOrFail($subjectId),
                $actor->id,
            ),
            'approval_chain' => $this->approveChainSubject($actor, $subjectId, $notes),
            default => throw ValidationException::withMessages([
                'kind' => __('Unsupported approval type.'),
            ]),
        };
    }

    public function reject(User $actor, string $kind, int $subjectId, string $reason): void
    {
        $this->assertCanAct($actor, $kind, 'reject');

        match ($kind) {
            'quotation' => $this->quotationApprovals->reject(
                Quotation::query()->findOrFail($subjectId),
                $actor,
                $reason,
            ),
            'purchase_order' => PurchaseOrderService::reject(
                PurchaseOrder::query()->findOrFail($subjectId),
                $actor,
                $reason,
            ),
            'leave' => $this->leaveRequests->reject(
                LeaveRequest::query()->findOrFail($subjectId),
                $actor,
                $reason,
            ),
            'credit_note' => $this->customerInvoices->cancel(
                CustomerInvoice::query()->findOrFail($subjectId),
                $actor->id,
                $reason,
            ),
            'customer_payment' => $this->customerPayments->cancel(
                CustomerPayment::query()->findOrFail($subjectId),
                $actor->id,
                $reason,
            ),
            'supplier_payment' => $this->supplierPayments->cancel(
                SupplierPayment::query()->findOrFail($subjectId),
                $actor->id,
                $reason,
            ),
            'approval_chain' => $this->rejectChainSubject($actor, $subjectId, $reason),
            default => throw ValidationException::withMessages([
                'kind' => __('Rejection is not supported for this document type.'),
            ]),
        };
    }

    public function escalate(User $actor, string $kind, int $subjectId, ?int $chainRunId = null): void
    {
        if (! $actor->can('governance.escalations.manage') && ! $actor->can('executive.approvals.action')) {
            throw ValidationException::withMessages([
                'escalation' => __('You are not authorized to escalate approvals.'),
            ]);
        }

        $run = $this->resolveChainRun($kind, $subjectId, $chainRunId);
        $record = ApprovalChainStepRecord::query()
            ->where('approval_chain_run_id', $run->id)
            ->where('status', ApprovalChainStepStatus::Pending)
            ->orderBy('step_order')
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages([
                'escalation' => __('No pending approval step found to escalate.'),
            ]);
        }

        $rule = $this->escalations->resolveRuleForStepRecord($record);

        if ($rule === null) {
            throw ValidationException::withMessages([
                'escalation' => __('No escalation rule applies to this approval step.'),
            ]);
        }

        if (! $this->escalationEngine->processAutoEscalation($record, $rule)) {
            throw ValidationException::withMessages([
                'escalation' => __('This approval step has already been escalated.'),
            ]);
        }
    }

    public function delegateRedirect(User $actor, string $kind, int $subjectId): RedirectResponse
    {
        if (! $actor->can('governance.delegations.view') && ! $actor->can('governance.delegations.create')) {
            throw ValidationException::withMessages([
                'delegation' => __('You are not authorized to manage delegations.'),
            ]);
        }

        if (! Route::has('admin.governance.delegations.create')) {
            throw ValidationException::withMessages([
                'delegation' => __('Delegation workspace is not available.'),
            ]);
        }

        return redirect()->route('admin.governance.delegations.create', [
            'context_kind' => $kind,
            'context_subject_id' => $subjectId,
        ]);
    }

    protected function approveLeave(User $actor, int $subjectId): void
    {
        $leave = LeaveRequest::query()->with('leaveType')->findOrFail($subjectId);

        if ($leave->status === LeaveRequestStatus::Submitted && $leave->leaveType?->requires_supervisor_approval) {
            $this->leaveRequests->approveSupervisor($leave, $actor);

            return;
        }

        $this->leaveRequests->approveHr($leave, $actor);
    }

    protected function approveArtwork(User $actor, int $subjectId, ?string $notes): void
    {
        $request = ArtworkRequest::query()->findOrFail($subjectId);

        if ($request->status !== ArtworkRequestStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted artwork can be approved.'),
            ]);
        }

        $version = $request->currentVersionRecord();

        if ($version === null) {
            throw ValidationException::withMessages([
                'decision' => __('No version available for approval.'),
            ]);
        }

        ArtworkApproval::query()->create([
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'artwork_request_id' => $request->id,
            'artwork_version_id' => $version->id,
            'approved_by' => $actor->id,
            'decision' => ArtworkApprovalDecision::Approved,
            'comments' => $notes,
        ]);

        $request->transitionTo(ArtworkRequestStatus::Approved);
    }

    protected function postJournal(User $actor, int $subjectId): void
    {
        $journal = Journal::query()->findOrFail($subjectId);
        $this->journalPosting->post($journal, $actor->id);
    }

    protected function approveChainSubject(User $actor, int $subjectId, ?string $notes): void
    {
        $subject = $this->resolveSubjectFromPendingRun($subjectId);
        $this->approvalEngine->recordApproval($subject, $actor, $notes);
    }

    protected function rejectChainSubject(User $actor, int $subjectId, string $reason): void
    {
        $subject = $this->resolveSubjectFromPendingRun($subjectId);
        $this->approvalEngine->recordRejection($subject, $actor, $reason);
    }

    protected function resolveSubjectFromPendingRun(int $subjectId): Model
    {
        $run = ApprovalChainRun::query()
            ->where('subject_id', $subjectId)
            ->where('status', ApprovalChainRunStatus::Pending)
            ->latest('id')
            ->first();

        if ($run === null || $run->subject === null) {
            throw ValidationException::withMessages([
                'subject' => __('Pending approval chain not found.'),
            ]);
        }

        return $run->subject;
    }

    protected function resolveChainRun(string $kind, int $subjectId, ?int $chainRunId): ApprovalChainRun
    {
        if ($chainRunId !== null) {
            $run = ApprovalChainRun::query()->findOrFail($chainRunId);

            if ($run->status !== ApprovalChainRunStatus::Pending) {
                throw ValidationException::withMessages([
                    'escalation' => __('Approval chain is not pending.'),
                ]);
            }

            return $run;
        }

        $subject = match ($kind) {
            'quotation' => Quotation::query()->findOrFail($subjectId),
            'purchase_order' => PurchaseOrder::query()->findOrFail($subjectId),
            'stock_adjustment' => StockAdjustment::query()->findOrFail($subjectId),
            'journal' => Journal::query()->findOrFail($subjectId),
            'customer_payment' => CustomerPayment::query()->findOrFail($subjectId),
            'supplier_payment' => SupplierPayment::query()->findOrFail($subjectId),
            'approval_chain' => $this->resolveSubjectFromPendingRun($subjectId),
            default => null,
        };

        if ($subject === null) {
            throw ValidationException::withMessages([
                'escalation' => __('Escalation is not available for this document type.'),
            ]);
        }

        $run = $this->approvalEngine->latestRun($subject);

        if ($run === null || $run->status !== ApprovalChainRunStatus::Pending) {
            throw ValidationException::withMessages([
                'escalation' => __('No pending approval chain found for this document.'),
            ]);
        }

        return $run;
    }

    protected function assertCanAct(User $actor, string $kind, string $action): void
    {
        if ($actor->can('executive.approvals.action')) {
            return;
        }

        $permission = match ([$kind, $action]) {
            ['quotation', 'approve'] => 'quotations.approve',
            ['quotation', 'reject'] => 'quotations.edit',
            ['artwork', 'approve'] => 'artwork.approve',
            ['purchase_request', 'approve'] => 'procurement.requests.approve',
            ['purchase_order', 'approve'], ['purchase_order', 'reject'] => 'procurement.orders.approve',
            ['leave', 'approve'], ['leave', 'reject'] => 'hr.leave.approve',
            ['payroll', 'approve'] => 'hr.payroll.approve',
            ['compensation', 'approve'] => 'hr.compensation.approve',
            ['stock_adjustment', 'approve'] => 'inventory.reconcile.approve',
            ['credit_note', 'approve'] => 'invoices.approve',
            ['credit_note', 'reject'] => 'invoices.cancel',
            ['journal', 'approve'] => 'accounting.journals.post',
            ['customer_payment', 'approve'] => 'payments.post',
            ['customer_payment', 'reject'] => 'payments.cancel',
            ['supplier_payment', 'approve'] => 'payables.payments.post',
            ['supplier_payment', 'reject'] => 'payables.payments.cancel',
            ['approval_chain', 'approve'], ['approval_chain', 'reject'] => 'governance.chains.view',
            default => null,
        };

        if ($permission === null || ! $actor->can($permission)) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to perform this action.'),
            ]);
        }
    }
}
