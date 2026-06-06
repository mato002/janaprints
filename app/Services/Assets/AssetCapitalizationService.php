<?php

namespace App\Services\Assets;

use App\Enums\ApprovalRuleType;
use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetWarrantyStatus;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Enums\ProcurementItemClassification;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetProcurementDocument;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\GoodsReceiptItem;
use App\Models\User;
use App\Support\Accounting\AssetAcquisitionPostingService;
use App\Support\ActivityLogger;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetCapitalizationService
{
    public function __construct(
        protected AssetRegisterService $register,
        protected AssetFinanceTimelineService $timeline,
        protected AssetAcquisitionPostingService $acquisitionPosting,
        protected ApprovalRulesService $approvals,
        protected AssetPeriodControlService $periodControl,
    ) {}

    public function createCandidateFromReceiptLine(GoodsReceipt $receipt, GoodsReceiptItem $line): AssetCapitalizationCandidate
    {
        if (! $line->isCapitalizable()) {
            throw ValidationException::withMessages([
                'line' => __('Line is not classified as a capital asset.'),
            ]);
        }

        $existing = AssetCapitalizationCandidate::query()
            ->where('goods_receipt_item_id', $line->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $receipt->loadMissing(['purchaseOrder.vendor', 'purchaseOrder.purchaseRequest']);
        $poItem = $line->purchaseOrderItem;
        $lineAmount = round((float) $line->quantity_received * (float) $line->unit_cost, 2);

        return AssetCapitalizationCandidate::query()->create([
            'company_id' => $receipt->company_id,
            'branch_id' => $receipt->branch_id,
            'candidate_number' => app(NumberingService::class)->next(
                DocumentType::AssetCapitalizationCandidate,
                $receipt->company_id,
                $receipt->branch_id,
            ),
            'goods_receipt_id' => $receipt->id,
            'goods_receipt_item_id' => $line->id,
            'purchase_order_id' => $receipt->purchase_order_id,
            'purchase_order_item_id' => $line->purchase_order_item_id,
            'vendor_id' => $receipt->purchaseOrder?->vendor_id,
            'asset_category_id' => $line->asset_category_id ?? $poItem?->asset_category_id,
            'quantity' => $line->quantity_received,
            'quantity_capitalized' => 0,
            'unit_cost' => $line->unit_cost,
            'line_amount' => $lineAmount,
            'status' => CapitalizationCandidateStatus::Ready,
            'received_date' => $receipt->receipt_date,
        ]);
    }

    /**
     * @param  array<string, mixed>  $workbench
     * @return list<FixedAsset>
     */
    public function capitalize(AssetCapitalizationCandidate $candidate, array $workbench, int $userId, bool $postJournal = true): array
    {
        if (! in_array($candidate->status, [CapitalizationCandidateStatus::Pending, CapitalizationCandidateStatus::Ready], true)) {
            throw ValidationException::withMessages([
                'candidate' => __('Candidate is not open for capitalization.'),
            ]);
        }

        $quantity = (int) ($workbench['quantity'] ?? $candidate->remainingQuantity());

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => __('Capitalization quantity must be at least one.'),
            ]);
        }

        if ($quantity > $candidate->remainingQuantity()) {
            throw ValidationException::withMessages([
                'quantity' => __('Quantity exceeds remaining received quantity.'),
            ]);
        }

        $requiresApproval = $this->requiresApprovalFor($candidate, $quantity);

        if ($requiresApproval) {
            if (! $candidate->approved_at) {
                throw ValidationException::withMessages([
                    'approval' => __('Capitalization requires authorized approval before execution.'),
                ]);
            }

            $actor = User::query()->find($userId);
            if ($candidate->approved_by === $userId && ! $actor?->hasRole('Super Admin')) {
                throw ValidationException::withMessages([
                    'approval' => __('You cannot capitalize an item you approved. Another authorized user must execute capitalization.'),
                ]);
            }
        }

        $candidate->load([
            'goodsReceipt.purchaseOrder.purchaseRequest',
            'goodsReceiptItem.purchaseOrderItem',
            'vendor',
            'category',
        ]);

        return DB::transaction(function () use ($candidate, $workbench, $userId, $postJournal, $quantity, $requiresApproval) {
            $assets = [];
            $unitCost = (float) ($workbench['unit_cost'] ?? $candidate->unit_cost);
            $categoryId = (int) ($workbench['asset_category_id'] ?? $candidate->asset_category_id);

            if (! $categoryId) {
                throw ValidationException::withMessages([
                    'asset_category_id' => __('Asset category is required for capitalization.'),
                ]);
            }

            $baseName = $workbench['asset_name'] ?? $candidate->goodsReceiptItem?->purchaseOrderItem?->description ?? __('Capital Asset');
            $serials = $workbench['serial_numbers'] ?? [];
            $branchId = (int) ($workbench['branch_id'] ?? $candidate->branch_id);
            $custodianId = $workbench['assigned_custodian_user_id'] ?? $workbench['assigned_to_user_id'] ?? null;

            for ($i = 0; $i < $quantity; $i++) {
                $suffix = $quantity > 1 ? ' #'.($i + 1) : '';
                $asset = $this->register->createFromProcurement([
                    'asset_category_id' => $categoryId,
                    'branch_id' => $branchId,
                    'asset_name' => $baseName.$suffix,
                    'serial_number' => $serials[$i] ?? ($workbench['serial_number'] ?? null),
                    'manufacturer' => $workbench['manufacturer'] ?? null,
                    'model' => $workbench['model'] ?? null,
                    'acquisition_date' => $candidate->received_date->toDateString(),
                    'capitalization_date' => $candidate->received_date->toDateString(),
                    'acquisition_cost' => $unitCost,
                    'residual_value' => (float) ($workbench['residual_value'] ?? 0),
                    'useful_life_years' => $workbench['useful_life_years'] ?? null,
                    'depreciation_method' => $workbench['depreciation_method'] ?? null,
                    'assigned_custodian_user_id' => $custodianId,
                    'assigned_to_user_id' => $custodianId,
                    'status' => FixedAssetStatus::Active,
                    'vendor_id' => $candidate->vendor_id,
                    'purchase_request_id' => $candidate->goodsReceipt?->purchaseOrder?->purchase_request_id,
                    'purchase_order_id' => $candidate->purchase_order_id,
                    'goods_receipt_id' => $candidate->goods_receipt_id,
                    'goods_receipt_item_id' => $candidate->goods_receipt_item_id,
                    'capitalization_candidate_id' => $candidate->id,
                    'acquisition_source' => AssetAcquisitionSource::Procurement,
                ], $candidate->company_id, $branchId, $userId);

                $this->linkProcurementDocuments($asset, $candidate);

                if (! empty($workbench['warranty_end'])) {
                    $this->createWarranty($asset, [
                        'vendor_id' => $candidate->vendor_id,
                        'warranty_start' => $workbench['warranty_start'] ?? $candidate->received_date->toDateString(),
                        'warranty_end' => $workbench['warranty_end'],
                        'coverage' => $workbench['warranty_coverage'] ?? null,
                        'support_contact' => $workbench['warranty_support_contact'] ?? null,
                        'reference_number' => $workbench['warranty_reference'] ?? null,
                    ]);
                }

                if ($postJournal) {
                    $journal = $this->acquisitionPosting->postAcquisition($asset, $userId);
                    $this->timeline->record($asset, 'posted', __('Acquisition journal posted'), null, $journal, $userId);
                }

                $assets[] = $asset->fresh();
            }

            $candidate->update([
                'quantity_capitalized' => (float) $candidate->quantity_capitalized + $quantity,
                'asset_category_id' => $categoryId,
                'status' => ((float) $candidate->quantity_capitalized + $quantity) >= (float) $candidate->quantity
                    ? CapitalizationCandidateStatus::Capitalized
                    : CapitalizationCandidateStatus::Ready,
                'capitalized_at' => now(),
                'capitalized_by' => $userId,
            ]);

            ActivityLogger::log('capitalized', $candidate, $userId, [
                'quantity' => $quantity,
                'asset_ids' => collect($assets)->pluck('id')->all(),
            ]);

            return $assets;
        });
    }

    public function approve(AssetCapitalizationCandidate $candidate, int $approverId): AssetCapitalizationCandidate
    {
        if ($candidate->approved_at) {
            throw ValidationException::withMessages([
                'approval' => __('This capitalization candidate is already approved.'),
            ]);
        }

        if (! in_array($candidate->status, [CapitalizationCandidateStatus::Pending, CapitalizationCandidateStatus::Ready], true)) {
            throw ValidationException::withMessages([
                'candidate' => __('Candidate is not open for approval.'),
            ]);
        }

        $approver = User::query()->findOrFail($approverId);

        if ($candidate->capitalized_by === $approverId && ! $approver->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'approval' => __('You cannot approve your own capitalization request.'),
            ]);
        }

        $candidate->update([
            'status' => CapitalizationCandidateStatus::Pending,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        ActivityLogger::log('approved', $candidate, $approverId, [
            'candidate_number' => $candidate->candidate_number,
            'line_amount' => $candidate->line_amount,
        ]);

        return $candidate->fresh();
    }

    public function requiresApprovalFor(AssetCapitalizationCandidate $candidate, int $quantity = 1): bool
    {
        if ($quantity > 1) {
            return true;
        }

        return $this->approvals->requiresApproval(
            ApprovalRuleType::AssetCapitalizationApproval,
            (float) $candidate->line_amount,
            null,
            $candidate->company_id,
            $candidate->branch_id,
        );
    }

    public function reject(AssetCapitalizationCandidate $candidate, string $reason, int $userId): AssetCapitalizationCandidate
    {
        $candidate->update([
            'status' => CapitalizationCandidateStatus::Rejected,
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        ActivityLogger::log('rejected', $candidate, $userId, [
            'reason' => $reason,
        ]);

        return $candidate->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createWarranty(FixedAsset $asset, array $data): AssetWarranty
    {
        $status = AssetWarrantyStatus::Active;

        if (! empty($data['warranty_end']) && now()->startOfDay()->gt($data['warranty_end'])) {
            $status = AssetWarrantyStatus::Expired;
        }

        return AssetWarranty::query()->create([
            'company_id' => $asset->company_id,
            'fixed_asset_id' => $asset->id,
            'vendor_id' => $data['vendor_id'] ?? $asset->vendor_id,
            'warranty_start' => $data['warranty_start'],
            'warranty_end' => $data['warranty_end'],
            'coverage' => $data['coverage'] ?? null,
            'support_contact' => $data['support_contact'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    protected function linkProcurementDocuments(FixedAsset $asset, AssetCapitalizationCandidate $candidate): void
    {
        $candidate->loadMissing('goodsReceipt.purchaseOrder.purchaseRequest');

        $links = [
            [GoodsReceipt::class, $candidate->goods_receipt_id, $candidate->goodsReceipt?->receipt_number],
            [\App\Models\Procurement\PurchaseOrder::class, $candidate->purchase_order_id, $candidate->goodsReceipt?->purchaseOrder?->po_number],
        ];

        if ($candidate->goodsReceipt?->purchaseOrder?->purchase_request_id) {
            $links[] = [
                \App\Models\Procurement\PurchaseRequest::class,
                $candidate->goodsReceipt->purchaseOrder->purchase_request_id,
                $candidate->goodsReceipt->purchaseOrder->purchaseRequest?->request_number,
            ];
        }

        foreach ($links as [$type, $id, $label]) {
            if (! $id) {
                continue;
            }

            AssetProcurementDocument::query()->firstOrCreate([
                'fixed_asset_id' => $asset->id,
                'document_type' => $type,
                'document_id' => $id,
            ], [
                'document_label' => $label,
            ]);
        }
    }

    /**
     * @return Collection<int, AssetCapitalizationCandidate>
     */
    public function pendingQueue(int $companyId, ?int $branchId = null): Collection
    {
        return AssetCapitalizationCandidate::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', [
                CapitalizationCandidateStatus::Pending->value,
                CapitalizationCandidateStatus::Ready->value,
            ])
            ->with(['vendor', 'category', 'goodsReceipt', 'purchaseOrder'])
            ->latest('received_date')
            ->get();
    }

    public static function isCapitalLine(GoodsReceiptItem $line): bool
    {
        if ($line->item_classification) {
            return $line->item_classification->isCapitalizable();
        }

        return $line->purchaseOrderItem?->isCapitalizable() ?? false;
    }

    public static function classificationFromPoItem(?ProcurementItemClassification $classification): ProcurementItemClassification
    {
        return $classification ?? ProcurementItemClassification::InventoryItem;
    }
}
