<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Assets\AssetCapitalizationCandidate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProcurementApprovalQueueService
{
    /**
     * @return list<ApprovalRuleType>
     */
    public function procurementRuleTypes(): array
    {
        return [
            ApprovalRuleType::PurchaseRequestApproval,
            ApprovalRuleType::ProcurementApproval,
            ApprovalRuleType::RfqApproval,
            ApprovalRuleType::GoodsReceiptApproval,
            ApprovalRuleType::VendorInvoiceApproval,
            ApprovalRuleType::PaymentApproval,
            ApprovalRuleType::AssetCapitalizationApproval,
        ];
    }

    /**
     * @return array{
     *     pending: Collection<int, array<string, mixed>>,
     *     aging: Collection<int, array<string, mixed>>,
     *     escalated: Collection<int, array<string, mixed>>,
     *     rejected: Collection<int, array<string, mixed>>,
     * }
     */
    public function present(int $companyId, ?int $branchId = null): array
    {
        $pendingRuns = $this->pendingRuns($companyId, $branchId);
        $pending = $pendingRuns->map(fn (ApprovalChainRun $run) => $this->mapRun($run, 'pending'));
        $aging = $pendingRuns
            ->filter(fn (ApprovalChainRun $run) => $run->created_at?->lte(now()->subDays(3)))
            ->map(fn (ApprovalChainRun $run) => $this->mapRun($run, 'aging'));
        $escalated = $this->escalatedSteps($companyId, $branchId)
            ->map(fn (ApprovalChainStepRecord $record) => $this->mapEscalatedStep($record));
        $rejected = $this->rejectedRuns($companyId, $branchId)
            ->map(fn (ApprovalChainRun $run) => $this->mapRun($run, 'rejected'));

        return [
            'pending' => $pending->values(),
            'aging' => $aging->values(),
            'escalated' => $escalated->values(),
            'rejected' => $rejected->values(),
        ];
    }

    /**
     * @return Collection<int, ApprovalChainRun>
     */
    protected function pendingRuns(int $companyId, ?int $branchId): Collection
    {
        return ApprovalChainRun::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('status', ApprovalChainRunStatus::Pending)
            ->whereIn('approval_rule_type', $this->procurementRuleTypes())
            ->with(['stepRecords.step', 'subject'])
            ->orderBy('created_at')
            ->limit(100)
            ->get();
    }

    /**
     * @return Collection<int, ApprovalChainStepRecord>
     */
    protected function escalatedSteps(int $companyId, ?int $branchId): Collection
    {
        return ApprovalChainStepRecord::query()
            ->where('status', ApprovalChainStepStatus::Escalated)
            ->whereHas('run', function ($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId)
                    ->when($branchId, fn ($inner) => $inner->where('branch_id', $branchId))
                    ->whereIn('approval_rule_type', $this->procurementRuleTypes());
            })
            ->with(['run.subject'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();
    }

    /**
     * @return Collection<int, ApprovalChainRun>
     */
    protected function rejectedRuns(int $companyId, ?int $branchId): Collection
    {
        return ApprovalChainRun::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('status', ApprovalChainRunStatus::Rejected)
            ->whereIn('approval_rule_type', $this->procurementRuleTypes())
            ->with('subject')
            ->where('updated_at', '>=', now()->subDays(14))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapRun(ApprovalChainRun $run, string $bucket): array
    {
        $subject = $run->subject;
        $document = $this->documentLabel($subject);

        return [
            'bucket' => $bucket,
            'run_id' => $run->id,
            'rule_type' => $run->approval_rule_type->value,
            'rule_label' => config("approval_registry.rule_types.{$run->approval_rule_type->value}.label", $run->approval_rule_type->value),
            'document' => $document['number'],
            'document_label' => $document['label'],
            'amount' => (float) ($run->context_json['amount'] ?? 0),
            'submitted_at' => $run->created_at,
            'age_days' => $run->created_at?->diffInDays(now()),
            'route' => $document['route'],
            'status' => $run->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapEscalatedStep(ApprovalChainStepRecord $record): array
    {
        $run = $record->run;
        $subject = $run?->subject;
        $document = $this->documentLabel($subject);

        return [
            'bucket' => 'escalated',
            'run_id' => $run?->id,
            'step_number' => $record->step_number,
            'rule_type' => $run?->approval_rule_type->value,
            'rule_label' => $run ? config("approval_registry.rule_types.{$run->approval_rule_type->value}.label", $run->approval_rule_type->value) : null,
            'document' => $document['number'],
            'document_label' => $document['label'],
            'submitted_at' => $record->created_at,
            'route' => $document['route'],
            'status' => $record->status->value,
        ];
    }

    /**
     * @return array{number: string, label: string, route: string|null}
     */
    protected function documentLabel(?Model $subject): array
    {
        return match (true) {
            $subject instanceof PurchaseRequest => [
                'number' => $subject->request_number,
                'label' => __('Purchase Request'),
                'route' => route('admin.procurement.requests.show', $subject),
            ],
            $subject instanceof PurchaseOrder => [
                'number' => $subject->po_number,
                'label' => __('Purchase Order'),
                'route' => route('admin.procurement.orders.show', $subject),
            ],
            $subject instanceof Rfq => [
                'number' => $subject->rfq_number,
                'label' => __('RFQ'),
                'route' => route('admin.procurement.rfqs.show', $subject),
            ],
            $subject instanceof GoodsReceipt => [
                'number' => $subject->receipt_number,
                'label' => __('Goods Receipt'),
                'route' => route('admin.procurement.receipts.show', $subject),
            ],
            $subject instanceof SupplierBill => [
                'number' => $subject->bill_number,
                'label' => __('Supplier Bill'),
                'route' => null,
            ],
            $subject instanceof SupplierPayment => [
                'number' => $subject->payment_number,
                'label' => __('Supplier Payment'),
                'route' => null,
            ],
            $subject instanceof AssetCapitalizationCandidate => [
                'number' => $subject->candidate_number,
                'label' => __('Asset Capitalization'),
                'route' => route('admin.assets.acquisitions.queue'),
            ],
            default => [
                'number' => __('Unknown'),
                'label' => __('Procurement document'),
                'route' => null,
            ],
        };
    }
}
