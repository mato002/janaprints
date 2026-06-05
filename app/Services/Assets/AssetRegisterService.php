<?php

namespace App\Services\Assets;

use App\Enums\AssetAcquisitionSource;
use App\Enums\DepreciationMethod;
use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Support\ActivityLogger;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetRegisterService
{
    public function __construct(
        protected NumberGenerator $numberGenerator,
        protected AssetAssignmentService $assignments,
        protected AssetFinanceTimelineService $financeTimeline,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, ?int $branchId, int $userId): FixedAsset
    {
        return DB::transaction(function () use ($data, $companyId, $branchId, $userId) {
            $assetNumber = $this->numberGenerator->generate(
                DocumentType::FixedAsset,
                $companyId,
                null,
            );

            $category = AssetCategory::query()->findOrFail($data['asset_category_id']);
            $acquisitionCost = (float) $data['acquisition_cost'];
            $residual = (float) ($data['residual_value'] ?? 0);
            $capitalizationDate = $data['capitalization_date'] ?? $data['acquisition_date'];

            $asset = FixedAsset::query()->create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? $branchId,
                'asset_category_id' => $data['asset_category_id'],
                'asset_number' => $assetNumber,
                'asset_name' => $data['asset_name'],
                'barcode' => $data['barcode'] ?? $assetNumber,
                'serial_number' => $data['serial_number'] ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'model' => $data['model'] ?? null,
                'acquisition_date' => $data['acquisition_date'],
                'capitalization_date' => $capitalizationDate,
                'acquisition_cost' => $acquisitionCost,
                'residual_value' => $residual,
                'useful_life_years' => $data['useful_life_years'] ?? $category->useful_life_years,
                'depreciation_method' => $data['depreciation_method'] ?? $category->depreciation_method ?? DepreciationMethod::StraightLine,
                'depreciation_start_date' => $data['depreciation_start_date'] ?? $capitalizationDate,
                'accumulated_depreciation' => 0,
                'net_book_value' => $acquisitionCost,
                'status' => $data['status'] ?? FixedAssetStatus::Active,
                'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
                'assigned_to_branch_id' => $data['assigned_to_branch_id'] ?? null,
                'assigned_custodian_user_id' => $data['assigned_custodian_user_id'] ?? null,
                'acquisition_source' => $data['acquisition_source'] ?? AssetAcquisitionSource::Manual,
                'vendor_id' => $data['vendor_id'] ?? null,
                'purchase_request_id' => $data['purchase_request_id'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'goods_receipt_id' => $data['goods_receipt_id'] ?? null,
                'goods_receipt_item_id' => $data['goods_receipt_item_id'] ?? null,
                'supplier_bill_id' => $data['supplier_bill_id'] ?? null,
                'capitalization_candidate_id' => $data['capitalization_candidate_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->financeTimeline->record($asset, 'created', __('Asset registered'), null, null, $userId);
            $this->financeTimeline->record($asset, 'capitalized', __('Asset capitalized'), null, null, $userId, [
                'cost' => $acquisitionCost,
            ]);

            if (! empty($data['assigned_to_user_id'])) {
                $this->assignments->assignToUser($asset, (int) $data['assigned_to_user_id'], $userId);
            } elseif (! empty($data['assigned_to_branch_id'])) {
                $this->assignments->assignToBranch($asset, (int) $data['assigned_to_branch_id'], $userId);
            }

            return $asset->fresh(['category', 'branch', 'assignedUser']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromProcurement(array $data, int $companyId, ?int $branchId, int $userId): FixedAsset
    {
        $data['acquisition_source'] = AssetAcquisitionSource::Procurement;

        return DB::transaction(function () use ($data, $companyId, $branchId, $userId) {
            $asset = $this->create($data, $companyId, $branchId, $userId);

            $this->financeTimeline->record(
                $asset,
                'capitalized_from_procurement',
                __('Asset capitalized from procurement'),
                null,
                $asset->capitalizationCandidate,
                $userId,
                [
                    'purchase_order_id' => $data['purchase_order_id'] ?? null,
                    'goods_receipt_id' => $data['goods_receipt_id'] ?? null,
                ],
            );

            return $asset->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FixedAsset $asset, array $data, int $userId): FixedAsset
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Disposed assets cannot be edited.'),
            ]);
        }

        $immutable = ['asset_number', 'company_id'];
        $payload = collect($data)->except($immutable)->all();

        $asset->update($payload);

        ActivityLogger::log('updated', $asset->fresh(), $userId, [
            'changes' => array_keys($payload),
        ]);

        return $asset->fresh(['category', 'branch', 'assignedUser']);
    }

    public function changeStatus(FixedAsset $asset, FixedAssetStatus $status, int $userId): FixedAsset
    {
        $previous = $asset->status;
        $asset->update(['status' => $status]);

        ActivityLogger::log('status_changed', $asset->fresh(), $userId, [
            'from' => $previous->value,
            'to' => $status->value,
        ]);

        return $asset->fresh();
    }

    public function archive(FixedAsset $asset, int $userId): FixedAsset
    {
        $asset->update(['archived_at' => now()]);

        ActivityLogger::log('archived', $asset->fresh(), $userId);

        return $asset->fresh();
    }

    /**
     * @param  list<int>  $assetIds
     */
    public function bulkAssignUsers(array $assetIds, int $userId, int $assignedBy): int
    {
        $count = 0;

        FixedAsset::query()
            ->whereIn('id', $assetIds)
            ->forTenant()
            ->notArchived()
            ->each(function (FixedAsset $asset) use ($userId, $assignedBy, &$count) {
                $this->assignments->assignToUser($asset, $userId, $assignedBy);
                $count++;
            });

        return $count;
    }

    /**
     * @param  list<int>  $assetIds
     */
    public function bulkChangeStatus(array $assetIds, FixedAssetStatus $status, int $userId): int
    {
        $count = 0;

        FixedAsset::query()
            ->whereIn('id', $assetIds)
            ->forTenant()
            ->notArchived()
            ->each(function (FixedAsset $asset) use ($status, $userId, &$count) {
                $this->changeStatus($asset, $status, $userId);
                $count++;
            });

        return $count;
    }

    /**
     * @param  list<int>  $assetIds
     */
    public function bulkArchive(array $assetIds, int $userId): int
    {
        $count = 0;

        FixedAsset::query()
            ->whereIn('id', $assetIds)
            ->forTenant()
            ->notArchived()
            ->each(function (FixedAsset $asset) use ($userId, &$count) {
                $this->archive($asset, $userId);
                $count++;
            });

        return $count;
    }
}
