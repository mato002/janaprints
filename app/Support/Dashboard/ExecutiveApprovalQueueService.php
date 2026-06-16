<?php

namespace App\Support\Dashboard;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\CompensationStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Enums\JournalStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\StockAdjustmentStatus;
use App\Enums\SupplierPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Employee;
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
use App\Support\Reports\IntelligenceAggregateQueries;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ExecutiveApprovalQueueService
{
    public function __construct(
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?User $user = null): array
    {
        $user ??= auth()->user();

        if (! $user || ! $this->canViewQueue($user)) {
            return $this->emptyPayload();
        }

        $companyId = (int) (tenant()->companyId() ?? $user->company_id);
        $branchId = tenant()->branchId();

        $items = $this->collect($companyId, $branchId, $user);
        $summary = $this->summarize($items);

        return [
            'visible' => true,
            'available' => $items->isNotEmpty(),
            'items' => $items->values()->all(),
            'summary' => $summary,
            'queue_url' => Route::has('admin.executive.approvals.index')
                ? route('admin.executive.approvals.index')
                : null,
            'can_action' => $user->can('executive.approvals.action'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collect(int $companyId, ?int $branchId, User $user): Collection
    {
        $items = collect()
            ->merge($this->collectCommercial($companyId, $branchId, $user))
            ->merge($this->collectProcurement($companyId, $branchId, $user))
            ->merge($this->collectHr($companyId, $branchId, $user))
            ->merge($this->collectInventory($companyId, $branchId, $user))
            ->merge($this->collectFinance($companyId, $branchId, $user))
            ->merge($this->collectPendingChains($companyId, $branchId, $user));

        return $items
            ->unique(fn (array $row) => $row['kind'].':'.$row['subject_id'])
            ->sortBy([
                ['priority_rank', 'asc'],
                ['submitted_at', 'asc'],
            ])
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function summarize(Collection $items): array
    {
        $critical = $items->where('priority', 'critical')->count();
        $aging = $items->filter(fn (array $row) => ($row['age_days'] ?? 0) >= 3)->count();

        return [
            'waiting' => $items->count(),
            'critical' => $critical,
            'aging' => $aging,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectCommercial(int $companyId, ?int $branchId, User $user): Collection
    {
        $items = collect();

        if ($user->can('quotations.view')) {
            $items = $items->merge(
                Quotation::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', QuotationStatus::PendingApproval)
                    ->with(['customer:id,company_name', 'branch:id,name', 'preparer:id,name'])
                    ->orderBy('updated_at')
                    ->limit(30)
                    ->get()
                    ->map(fn (Quotation $q) => $this->item(
                        kind: 'quotation',
                        module: __('Commercial'),
                        document: $q->quotation_number,
                        documentLabel: __('Quotation'),
                        subjectId: $q->id,
                        requestedBy: $q->preparer?->name,
                        branch: $q->branch?->name,
                        value: (float) $q->total_amount,
                        submittedAt: $q->updated_at,
                        showRoute: 'admin.quotations.show',
                        showParams: $q,
                        approvePermission: 'quotations.approve',
                        rejectPermission: 'quotations.edit',
                        user: $user,
                    )),
            );
        }

        if ($user->can('artwork.view')) {
            $items = $items->merge(
                ArtworkRequest::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', ArtworkRequestStatus::Submitted)
                    ->with(['customer:id,company_name', 'branch:id,name', 'assignedDesigner:id,name'])
                    ->orderBy('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (ArtworkRequest $a) => $this->item(
                        kind: 'artwork',
                        module: __('Commercial'),
                        document: $a->request_number,
                        documentLabel: __('Artwork'),
                        subjectId: $a->id,
                        requestedBy: $a->assignedDesigner?->name,
                        branch: $a->branch?->name,
                        value: null,
                        submittedAt: $a->updated_at,
                        showRoute: 'admin.artwork.show',
                        showParams: $a,
                        approvePermission: 'artwork.approve',
                        rejectPermission: null,
                        user: $user,
                    )),
            );
        }

        return $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectProcurement(int $companyId, ?int $branchId, User $user): Collection
    {
        $items = collect();

        if ($user->can('procurement.requests.view')) {
            $items = $items->merge(
                PurchaseRequest::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->whereIn('status', [PurchaseRequestStatus::Submitted, PurchaseRequestStatus::PendingApproval])
                    ->with(['requester:id,name', 'branch:id,name'])
                    ->withSum('items', 'line_total')
                    ->orderBy('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (PurchaseRequest $pr) => $this->item(
                        kind: 'purchase_request',
                        module: __('Procurement'),
                        document: $pr->request_number,
                        documentLabel: __('Purchase Request'),
                        subjectId: $pr->id,
                        requestedBy: $pr->requester?->name,
                        branch: $pr->branch?->name,
                        value: (float) ($pr->items_sum_line_total ?? 0),
                        submittedAt: $pr->updated_at,
                        showRoute: 'admin.procurement.requests.show',
                        showParams: $pr,
                        approvePermission: 'procurement.requests.approve',
                        rejectPermission: 'procurement.requests.approve',
                        user: $user,
                    )),
            );
        }

        if ($user->can('procurement.orders.view')) {
            $items = $items->merge(
                PurchaseOrder::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', PurchaseOrderStatus::PendingApproval)
                    ->with(['preparer:id,name', 'branch:id,name', 'vendor:id,vendor_name'])
                    ->orderBy('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (PurchaseOrder $po) => $this->item(
                        kind: 'purchase_order',
                        module: __('Procurement'),
                        document: $po->po_number,
                        documentLabel: __('Purchase Order'),
                        subjectId: $po->id,
                        requestedBy: $po->preparer?->name,
                        branch: $po->branch?->name,
                        value: (float) $po->total_amount,
                        submittedAt: $po->updated_at,
                        showRoute: 'admin.procurement.orders.show',
                        showParams: $po,
                        approvePermission: 'procurement.orders.approve',
                        rejectPermission: 'procurement.orders.approve',
                        user: $user,
                    )),
            );
        }

        return $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectHr(int $companyId, ?int $branchId, User $user): Collection
    {
        $items = collect();

        if ($user->can('hr.leave.view')) {
            $items = $items->merge(
                LeaveRequest::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->whereIn('status', [LeaveRequestStatus::Submitted, LeaveRequestStatus::SupervisorApproved])
                    ->with(['employee:id,first_name,last_name', 'branch:id,name', 'submitter:id,name', 'leaveType:id,name,requires_supervisor_approval,requires_hr_approval'])
                    ->orderBy('submitted_at')
                    ->limit(25)
                    ->get()
                    ->map(function (LeaveRequest $leave) use ($user) {
                        $approvePermission = $leave->status === LeaveRequestStatus::Submitted
                            && $leave->leaveType?->requires_supervisor_approval
                            ? 'hr.leave.approve'
                            : 'hr.leave.approve';

                        return $this->item(
                            kind: 'leave',
                            module: __('HR'),
                            document: $leave->reference ?? ('LR-'.$leave->id),
                            documentLabel: __('Leave Request'),
                            subjectId: $leave->id,
                            requestedBy: $leave->submitter?->name ?? $this->employeeName($leave->employee),
                            branch: $leave->branch?->name,
                            value: (float) $leave->days_requested,
                            submittedAt: $leave->submitted_at ?? $leave->created_at,
                            showRoute: 'admin.hr.leave.show',
                            showParams: $leave,
                            approvePermission: $approvePermission,
                            rejectPermission: 'hr.leave.reject',
                            user: $user,
                        );
                    }),
            );
        }

        if ($user->can('hr.payroll.view')) {
            $items = $items->merge(
                PayrollRun::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', PayrollRunStatus::PendingApproval)
                    ->with(['processedBy:id,name', 'branch:id,name'])
                    ->orderByDesc('processed_at')
                    ->limit(10)
                    ->get()
                    ->map(fn (PayrollRun $run) => $this->item(
                        kind: 'payroll',
                        module: __('HR'),
                        document: $run->reference ?? ('PR-'.$run->id),
                        documentLabel: __('Payroll Run'),
                        subjectId: $run->id,
                        requestedBy: $run->processedBy?->name,
                        branch: $run->branch?->name,
                        value: (float) $run->net_total,
                        submittedAt: $run->processed_at ?? $run->updated_at,
                        showRoute: 'admin.hr.payroll.show',
                        showParams: $run,
                        approvePermission: 'hr.payroll.approve',
                        rejectPermission: null,
                        user: $user,
                    )),
            );
        }

        if ($user->can('hr.compensation.view')) {
            $items = $items->merge(
                EmployeeCompensation::query()
                    ->where('company_id', $companyId)
                    ->where('status', CompensationStatus::PendingApproval)
                    ->where('is_active', true)
                    ->with(['employee:id,first_name,last_name,branch_id', 'employee.branch:id,name', 'changedBy:id,name'])
                    ->orderBy('updated_at')
                    ->limit(15)
                    ->get()
                    ->map(fn (EmployeeCompensation $comp) => $this->item(
                        kind: 'compensation',
                        module: __('HR'),
                        document: $comp->employee?->employee_number ?? ('COMP-'.$comp->id),
                        documentLabel: __('Compensation'),
                        subjectId: $comp->id,
                        requestedBy: $comp->changedBy?->name,
                        branch: $comp->employee?->branch?->name,
                        value: (float) $comp->basic_salary,
                        submittedAt: $comp->updated_at,
                        showRoute: 'admin.hr.compensation.edit',
                        showParams: $comp->employee,
                        approvePermission: 'hr.compensation.approve',
                        rejectPermission: null,
                        user: $user,
                    )),
            );
        }

        return $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectInventory(int $companyId, ?int $branchId, User $user): Collection
    {
        if (! $user->can('inventory.view')) {
            return collect();
        }

        return StockAdjustment::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', StockAdjustmentStatus::Submitted)
            ->with(['submitter:id,name', 'warehouse:id,name,branch_id', 'items'])
            ->orderBy('submitted_at')
            ->limit(20)
            ->get()
            ->map(fn (StockAdjustment $adj) => $this->item(
                kind: 'stock_adjustment',
                module: __('Inventory'),
                document: $adj->adjustment_number,
                documentLabel: __('Stock Adjustment'),
                subjectId: $adj->id,
                requestedBy: $adj->submitter?->name,
                branch: $adj->warehouse?->name,
                value: \App\Support\StockAdjustmentService::totalValue($adj),
                submittedAt: $adj->submitted_at ?? $adj->updated_at,
                showRoute: 'admin.inventory.adjustments.show',
                showParams: $adj,
                approvePermission: 'inventory.reconcile.approve',
                rejectPermission: null,
                user: $user,
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectFinance(int $companyId, ?int $branchId, User $user): Collection
    {
        $items = collect();

        if ($user->can('invoices.view')) {
            $items = $items->merge(
                CustomerInvoice::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', CustomerInvoiceStatus::Draft)
                    ->where('invoice_type', CustomerInvoiceType::CreditNote)
                    ->with(['customer:id,company_name', 'branch:id,name', 'creator:id,name'])
                    ->orderBy('updated_at')
                    ->limit(15)
                    ->get()
                    ->map(fn (CustomerInvoice $inv) => $this->item(
                        kind: 'credit_note',
                        module: __('Finance'),
                        document: $inv->invoice_number,
                        documentLabel: __('Credit Note'),
                        subjectId: $inv->id,
                        requestedBy: $inv->creator?->name,
                        branch: $inv->branch?->name,
                        value: (float) $inv->total_amount,
                        submittedAt: $inv->updated_at,
                        showRoute: 'admin.invoices.show',
                        showParams: $inv,
                        approvePermission: 'invoices.approve',
                        rejectPermission: 'invoices.cancel',
                        user: $user,
                    )),
            );
        }

        if ($user->can('accounting.journals.view')) {
            $items = $items->merge(
                Journal::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where(function ($inner) use ($branchId) {
                        $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    }))
                    ->where('status', JournalStatus::Draft)
                    ->with(['creator:id,name', 'branch:id,name'])
                    ->orderByDesc('journal_date')
                    ->limit(15)
                    ->get()
                    ->map(fn (Journal $journal) => $this->item(
                        kind: 'journal',
                        module: __('Finance'),
                        document: $journal->journal_number,
                        documentLabel: __('Journal'),
                        subjectId: $journal->id,
                        requestedBy: $journal->creator?->name,
                        branch: $journal->branch?->name,
                        value: (float) $journal->total_debit,
                        submittedAt: $journal->created_at,
                        showRoute: 'admin.accounting.journals.show',
                        showParams: $journal,
                        approvePermission: 'accounting.journals.post',
                        rejectPermission: null,
                        user: $user,
                    )),
            );
        }

        if ($user->can('payments.view')) {
            $items = $items->merge(
                CustomerPayment::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', CustomerPaymentStatus::Draft)
                    ->with(['customer:id,company_name', 'branch:id,name', 'creator:id,name'])
                    ->orderByDesc('payment_date')
                    ->limit(15)
                    ->get()
                    ->map(fn (CustomerPayment $payment) => $this->item(
                        kind: 'customer_payment',
                        module: __('Finance'),
                        document: $payment->payment_number,
                        documentLabel: __('Customer Payment'),
                        subjectId: $payment->id,
                        requestedBy: $payment->creator?->name,
                        branch: $payment->branch?->name,
                        value: (float) $payment->amount,
                        submittedAt: $payment->created_at,
                        showRoute: 'admin.payments.show',
                        showParams: $payment,
                        approvePermission: 'payments.post',
                        rejectPermission: 'payments.cancel',
                        user: $user,
                    )),
            );
        }

        if ($user->can('payables.payments.view')) {
            $items = $items->merge(
                SupplierPayment::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', SupplierPaymentStatus::Draft)
                    ->with(['vendor:id,vendor_name', 'branch:id,name', 'creator:id,name'])
                    ->orderByDesc('payment_date')
                    ->limit(15)
                    ->get()
                    ->map(fn (SupplierPayment $payment) => $this->item(
                        kind: 'supplier_payment',
                        module: __('Finance'),
                        document: $payment->payment_number,
                        documentLabel: __('Supplier Payment'),
                        subjectId: $payment->id,
                        requestedBy: $payment->creator?->name,
                        branch: $payment->branch?->name,
                        value: (float) $payment->amount,
                        submittedAt: $payment->created_at,
                        showRoute: 'admin.payables.payments.show',
                        showParams: $payment,
                        approvePermission: 'payables.payments.post',
                        rejectPermission: 'payables.payments.cancel',
                        user: $user,
                    )),
            );
        }

        return $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectPendingChains(int $companyId, ?int $branchId, User $user): Collection
    {
        if (! $user->can('governance.chains.view') && ! $user->can('executive.approvals.view')) {
            return collect();
        }

        $excludedSubjectTypes = collect([
            Quotation::class,
            ArtworkRequest::class,
            PurchaseRequest::class,
            PurchaseOrder::class,
            LeaveRequest::class,
            PayrollRun::class,
            EmployeeCompensation::class,
            StockAdjustment::class,
            CustomerInvoice::class,
            Journal::class,
            CustomerPayment::class,
            SupplierPayment::class,
        ])->map(fn (string $class) => (new $class)->getMorphClass())->all();

        return ApprovalChainRun::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', ApprovalChainRunStatus::Pending)
            ->whereNotIn('subject_type', $excludedSubjectTypes)
            ->with(['branch:id,name', 'subject'])
            ->orderBy('started_at')
            ->limit(30)
            ->get()
            ->map(function (ApprovalChainRun $run) use ($user) {
                $subject = $run->subject;
                if ($subject === null) {
                    return null;
                }

                $document = $subject->quotation_number
                    ?? $subject->po_number
                    ?? $subject->order_number
                    ?? $subject->adjustment_number
                    ?? $subject->journal_number
                    ?? $subject->payment_number
                    ?? $subject->invoice_number
                    ?? $subject->bill_number
                    ?? ('#'.$subject->getKey());

                $permission = config("approval_registry.rule_types.{$run->approval_rule_type->value}.default_permission");

                return $this->item(
                    kind: 'approval_chain',
                    module: __('Governance'),
                    document: (string) $document,
                    documentLabel: config("approval_registry.rule_types.{$run->approval_rule_type->value}.label", $run->approval_rule_type->value),
                    subjectId: (int) $subject->getKey(),
                    requestedBy: null,
                    branch: $run->branch?->name,
                    value: (float) ($run->context_json['amount'] ?? 0),
                    submittedAt: $run->started_at,
                    showRoute: null,
                    showParams: null,
                    approvePermission: $permission,
                    rejectPermission: $permission,
                    user: $user,
                    chainRunId: $run->id,
                    chainSubjectType: $run->subject_type,
                );
            })
            ->filter();
    }

    /**
     * @return array<string, mixed>
     */
    protected function item(
        string $kind,
        string $module,
        string $document,
        string $documentLabel,
        int $subjectId,
        ?string $requestedBy,
        ?string $branch,
        ?float $value,
        ?CarbonInterface $submittedAt,
        ?string $showRoute,
        mixed $showParams,
        ?string $approvePermission,
        ?string $rejectPermission,
        User $user,
        ?int $chainRunId = null,
        ?string $chainSubjectType = null,
    ): array {
        $submittedAt ??= now();
        $ageDays = (int) $submittedAt->diffInDays(now());
        $priority = $this->priority($ageDays, $value);

        $canApprove = $approvePermission && ($user->can($approvePermission) || $user->can('executive.approvals.action'));
        $canReject = $rejectPermission && ($user->can($rejectPermission) || $user->can('executive.approvals.action'));
        $canEscalate = $user->can('governance.escalations.manage') || $user->can('executive.approvals.action');
        $canDelegate = $user->can('governance.delegations.view');

        return [
            'id' => $kind.':'.$subjectId,
            'kind' => $kind,
            'module' => $module,
            'document' => $document,
            'document_label' => $documentLabel,
            'subject_id' => $subjectId,
            'chain_run_id' => $chainRunId,
            'chain_subject_type' => $chainSubjectType,
            'requested_by' => $requestedBy ?? '—',
            'branch' => $branch ?? '—',
            'value' => $value,
            'value_display' => $value !== null ? $this->queries->money($value) : '—',
            'age_days' => $ageDays,
            'age_label' => $ageDays === 0 ? __('Today') : __(':count days', ['count' => $ageDays]),
            'priority' => $priority,
            'priority_rank' => match ($priority) {
                'critical' => 0,
                'high' => 1,
                default => 2,
            },
            'submitted_at' => $submittedAt->toIso8601String(),
            'show_url' => $showRoute && Route::has($showRoute) ? route($showRoute, $showParams) : null,
            'can_approve' => $canApprove,
            'can_reject' => $canReject,
            'can_escalate' => $canEscalate,
            'can_delegate' => $canDelegate,
        ];
    }

    protected function employeeName(?Employee $employee): ?string
    {
        if ($employee === null) {
            return null;
        }

        return trim("{$employee->first_name} {$employee->last_name}") ?: null;
    }

    protected function priority(int $ageDays, ?float $value): string
    {
        if ($ageDays >= 7 || ($value !== null && $value >= 250000)) {
            return 'critical';
        }

        if ($ageDays >= 3 || ($value !== null && $value >= 100000)) {
            return 'high';
        }

        return 'normal';
    }

    public function canView(User $user): bool
    {
        return $this->canViewQueue($user);
    }

    protected function canViewQueue(User $user): bool
    {
        if ($user->can('executive.approvals.view')) {
            return true;
        }

        return $user->can('commercial.approvals.view')
            || $user->can('quotations.approve')
            || $user->can('procurement.requests.approve')
            || $user->can('procurement.orders.approve')
            || $user->can('hr.leave.approve')
            || $user->can('hr.payroll.approve')
            || $user->can('hr.compensation.approve')
            || $user->can('inventory.reconcile.approve')
            || $user->can('invoices.approve')
            || $user->can('accounting.journals.post')
            || $user->can('payments.post');
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPayload(): array
    {
        return [
            'visible' => false,
            'available' => false,
            'items' => [],
            'summary' => ['waiting' => 0, 'critical' => 0, 'aging' => 0],
            'queue_url' => null,
            'can_action' => false,
        ];
    }
}
