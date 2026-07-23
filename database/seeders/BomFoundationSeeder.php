<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\User;
use App\Support\Production\ProductBomService;
use Illuminate\Database\Seeder;

class BomFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();
        $branch = Branch::query()->where('company_id', $company?->id)->first();
        $user = User::query()->where('company_id', $company?->id)->first();

        if ($company === null || $branch === null || $user === null) {
            return;
        }

        $sheet = UnitOfMeasure::query()->where('company_id', $company->id)->where('code', 'SHEET')->first();
        $litre = UnitOfMeasure::query()->where('company_id', $company->id)->where('code', 'LITRE')->first();
        $pack = UnitOfMeasure::query()->where('company_id', $company->id)->where('code', 'PACK')->first();

        if ($sheet === null || $litre === null || $pack === null) {
            return;
        }

        $paperCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'PAPER')->first();
        $inkCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'INK')->first();
        $packagingCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'PACKAGING')->first();
        $finishingCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'FINISHING')->first();
        $bcardCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'BCARD')->first();
        $flyerCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'FLYER')->first();
        $brochureCategory = InventoryCategory::query()->where('company_id', $company->id)->where('code', 'BROCHURE')->first();

        $paper = $this->item($company->id, $branch->id, $paperCategory?->id, $sheet->id, 'RAW-PAPER-350', 'Art Paper 350gsm', 12);
        $ink = $this->item($company->id, $branch->id, $inkCategory?->id, $litre->id, 'RAW-INK-CMYK', 'CMYK Process Ink', 45);
        $lamination = $this->item($company->id, $branch->id, $finishingCategory?->id, $sheet->id, 'RAW-LAM-SHEET', 'Lamination Film Sheet', 3);
        $packaging = $this->item($company->id, $branch->id, $packagingCategory?->id, $pack->id, 'RAW-PACK-BOX', 'Product Packaging Box', 2);
        $binding = $this->item($company->id, $branch->id, $finishingCategory?->id, $pack->id, 'RAW-BIND-SADDLE', 'Saddle Stitch Binding', 1.5);

        $businessCards = $this->item($company->id, $branch->id, $bcardCategory?->id, $pack->id, 'PROD-BCARD-STD', 'Business Cards — Standard', 0);
        $flyers = $this->item($company->id, $branch->id, $flyerCategory?->id, $pack->id, 'PROD-FLYER-A5', 'Flyers — A5', 0);
        $brochures = $this->item($company->id, $branch->id, $brochureCategory?->id, $pack->id, 'PROD-BROCHURE-8PP', 'Brochure — 8 Page', 0);

        $service = app(ProductBomService::class);

        $this->seedBom($service, $company->id, $branch->id, $user->id, $businessCards, 'Business Cards BOM', [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.25, 'waste_factor_percent' => 5],
            ['inventory_item_id' => $ink->id, 'quantity_per_unit' => 0.02, 'waste_factor_percent' => 3],
            ['inventory_item_id' => $lamination->id, 'quantity_per_unit' => 0.25, 'waste_factor_percent' => 2],
            ['inventory_item_id' => $packaging->id, 'quantity_per_unit' => 0.01, 'waste_factor_percent' => 0],
        ]);

        $this->seedBom($service, $company->id, $branch->id, $user->id, $flyers, 'Flyers BOM', [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.5, 'waste_factor_percent' => 4],
            ['inventory_item_id' => $ink->id, 'quantity_per_unit' => 0.03, 'waste_factor_percent' => 3],
            ['inventory_item_id' => $packaging->id, 'quantity_per_unit' => 0.01, 'waste_factor_percent' => 0],
        ]);

        $this->seedBom($service, $company->id, $branch->id, $user->id, $brochures, 'Brochure BOM', [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 2, 'waste_factor_percent' => 6],
            ['inventory_item_id' => $ink->id, 'quantity_per_unit' => 0.08, 'waste_factor_percent' => 4],
            ['inventory_item_id' => $binding->id, 'quantity_per_unit' => 1, 'waste_factor_percent' => 0],
            ['inventory_item_id' => $lamination->id, 'quantity_per_unit' => 2, 'waste_factor_percent' => 3],
            ['inventory_item_id' => $packaging->id, 'quantity_per_unit' => 0.02, 'waste_factor_percent' => 0],
        ]);

        $this->seedOpeningStock($company->id, $branch->id, $user->id, [
            $paper, $ink, $lamination, $packaging, $binding,
        ]);
    }

    /**
     * @param  list<InventoryItem>  $items
     */
    protected function seedOpeningStock(int $companyId, int $branchId, int $userId, array $items): void
    {
        $warehouse = \App\Models\Inventory\Warehouse::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_virtual', false)
            ->where('code', 'MAIN')
            ->first();

        if ($warehouse === null) {
            return;
        }

        $needsStock = collect($items)->filter(function (InventoryItem $item) use ($warehouse) {
            $balance = (float) \App\Models\Inventory\InventoryMovement::query()
                ->where('inventory_item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->sum('quantity');

            return $balance < 100;
        });

        if ($needsStock->isEmpty()) {
            return;
        }

        $receipt = \App\Models\Inventory\StockReceipt::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'OPEN-BOM-'.now()->format('YmdHis'),
            'source' => \App\Enums\StockReceiptSource::Adjustment,
            'receipt_date' => now()->toDateString(),
            'status' => \App\Enums\InventoryDocumentStatus::Draft,
            'notes' => 'BOM foundation opening stock',
            'received_by' => $userId,
        ]);

        foreach ($needsStock as $item) {
            $receipt->items()->create([
                'inventory_item_id' => $item->id,
                'quantity' => 5000,
                'unit_cost' => (float) ($item->standard_cost ?: 1),
            ]);
        }

        \App\Support\StockReceiptService::post($receipt->fresh(['items', 'warehouse']), $userId);
    }

    protected function item(int $companyId, int $branchId, ?int $categoryId, int $uomId, string $sku, string $name, float $cost): InventoryItem
    {
        $stockRole = str_starts_with($sku, 'PROD-') || str_starts_with($sku, 'BCARD-') || str_starts_with($sku, 'FLYER-') || str_starts_with($sku, 'BROCHURE-')
            ? \App\Enums\InventoryStockRole::FinishedGood
            : \App\Enums\InventoryStockRole::RawMaterial;

        return InventoryItem::query()->updateOrCreate(
            ['company_id' => $companyId, 'branch_id' => $branchId, 'sku' => $sku],
            [
                'inventory_category_id' => $categoryId,
                'unit_of_measure_id' => $uomId,
                'item_name' => $name,
                'standard_cost' => $cost,
                'is_active' => true,
                'stock_role' => $stockRole,
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function seedBom(
        ProductBomService $service,
        int $companyId,
        int $branchId,
        int $userId,
        InventoryItem $finished,
        string $name,
        array $lines,
    ): void {
        if (\App\Models\Production\ProductBom::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('finished_item_id', $finished->id)
            ->exists()) {
            return;
        }

        $service->create($companyId, $branchId, $userId, [
            'finished_item_id' => $finished->id,
            'name' => $name,
            'is_active' => true,
        ], $lines);
    }
}
