<?php

namespace App\Support\Procurement;

use App\Enums\ProcurementItemClassification;
use App\Models\Assets\AssetCategory;
use App\Models\Procurement\GoodsReceiptItem;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\PurchaseRequestItem;
use App\Models\Procurement\RfqItem;

class ProcurementLineClassificationMapper
{
    /**
     * @return array{
     *     item_classification: ProcurementItemClassification,
     *     asset_category_id: int|null,
     *     capitalization_required: bool,
     *     asset_useful_life: int|null,
     *     asset_depreciation_method: string|null,
     * }
     */
    public static function fromPurchaseRequestItem(PurchaseRequestItem $item): array
    {
        $item->loadMissing('assetCategory');

        return self::build(
            $item->item_classification ?? ProcurementItemClassification::InventoryItem,
            $item->asset_category_id,
            $item->capitalization_required ?? null,
            $item->asset_useful_life,
            $item->asset_depreciation_method,
            $item->assetCategory,
        );
    }

    /**
     * @return array{
     *     item_classification: ProcurementItemClassification,
     *     asset_category_id: int|null,
     *     capitalization_required: bool,
     *     asset_useful_life: int|null,
     *     asset_depreciation_method: string|null,
     * }
     */
    public static function fromRfqItem(RfqItem $item): array
    {
        $item->loadMissing(['assetCategory', 'purchaseRequestItem.assetCategory']);

        if ($item->item_classification !== null) {
            return self::build(
                $item->item_classification,
                $item->asset_category_id,
                $item->capitalization_required ?? null,
                $item->asset_useful_life,
                $item->asset_depreciation_method,
                $item->assetCategory,
            );
        }

        if ($item->purchaseRequestItem) {
            return self::fromPurchaseRequestItem($item->purchaseRequestItem);
        }

        return self::build(ProcurementItemClassification::InventoryItem, null, false, null, null, null);
    }

    /**
     * @return array{
     *     item_classification: ProcurementItemClassification,
     *     asset_category_id: int|null,
     *     capitalization_required: bool,
     *     asset_useful_life: int|null,
     *     asset_depreciation_method: string|null,
     * }
     */
    public static function fromPurchaseOrderItem(PurchaseOrderItem $item): array
    {
        $item->loadMissing('assetCategory');

        return self::build(
            $item->item_classification ?? ProcurementItemClassification::InventoryItem,
            $item->asset_category_id,
            $item->capitalization_required ?? null,
            $item->asset_useful_life,
            $item->asset_depreciation_method,
            $item->assetCategory,
        );
    }

    /**
     * @return array{
     *     item_classification: ProcurementItemClassification,
     *     asset_category_id: int|null,
     *     capitalization_required: bool,
     *     asset_useful_life: int|null,
     *     asset_depreciation_method: string|null,
     * }
     */
    public static function fromGoodsReceiptItem(GoodsReceiptItem $item): array
    {
        $item->loadMissing(['assetCategory', 'purchaseOrderItem.assetCategory']);

        if ($item->item_classification !== null) {
            return self::build(
                $item->item_classification,
                $item->asset_category_id,
                $item->capitalization_required ?? null,
                $item->asset_useful_life,
                $item->asset_depreciation_method,
                $item->assetCategory,
            );
        }

        if ($item->purchaseOrderItem) {
            return self::fromPurchaseOrderItem($item->purchaseOrderItem);
        }

        return self::build(ProcurementItemClassification::InventoryItem, null, false, null, null, null);
    }

    /**
     * @return array{
     *     item_classification: ProcurementItemClassification,
     *     asset_category_id: int|null,
     *     capitalization_required: bool,
     *     asset_useful_life: int|null,
     *     asset_depreciation_method: string|null,
     * }
     */
    protected static function build(
        ProcurementItemClassification $classification,
        ?int $assetCategoryId,
        ?bool $capitalizationRequired,
        ?int $assetUsefulLife,
        ?string $assetDepreciationMethod,
        ?AssetCategory $category,
    ): array {
        $capitalizationRequired ??= $classification->isCapitalizable();

        return [
            'item_classification' => $classification,
            'asset_category_id' => $assetCategoryId,
            'capitalization_required' => $capitalizationRequired,
            'asset_useful_life' => $assetUsefulLife ?? ($category ? $category->usefulLifeMonths() : null),
            'asset_depreciation_method' => $assetDepreciationMethod ?? $category?->depreciation_method,
        ];
    }
}
