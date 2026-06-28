<?php

namespace App\Support\Production;

use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductBom;
use App\Models\Production\ProductBomLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductBomService
{
    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function create(int $companyId, int $branchId, int $userId, array $header, array $lines): ProductBom
    {
        $this->validateLines($companyId, $branchId, (int) $header['finished_item_id'], $lines);

        return DB::transaction(function () use ($companyId, $branchId, $userId, $header, $lines) {
            $bom = ProductBom::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'finished_item_id' => $header['finished_item_id'],
                'name' => $header['name'],
                'version' => $header['version'] ?? 1,
                'is_active' => $header['is_active'] ?? true,
                'notes' => $header['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $this->syncLines($bom, $lines);

            return $bom->fresh(['finishedItem', 'lines.inventoryItem']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function update(ProductBom $bom, array $header, array $lines): ProductBom
    {
        $this->validateLines(
            $bom->company_id,
            $bom->branch_id,
            (int) ($header['finished_item_id'] ?? $bom->finished_item_id),
            $lines,
            $bom->id,
        );

        return DB::transaction(function () use ($bom, $header, $lines) {
            $bom->update([
                'finished_item_id' => $header['finished_item_id'] ?? $bom->finished_item_id,
                'name' => $header['name'] ?? $bom->name,
                'version' => $header['version'] ?? $bom->version,
                'is_active' => $header['is_active'] ?? $bom->is_active,
                'notes' => $header['notes'] ?? $bom->notes,
            ]);

            $bom->lines()->delete();
            $this->syncLines($bom, $lines);

            return $bom->fresh(['finishedItem', 'lines.inventoryItem']);
        });
    }

    public function findActiveForFinishedItem(int $companyId, int $branchId, int $finishedItemId): ?ProductBom
    {
        return ProductBom::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('finished_item_id', $finishedItemId)
            ->where('is_active', true)
            ->with(['lines.inventoryItem.unitOfMeasure'])
            ->first();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function syncLines(ProductBom $bom, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $bom->lines()->create([
                'inventory_item_id' => $line['inventory_item_id'],
                'quantity_per_unit' => $line['quantity_per_unit'],
                'quantity_formula' => $line['quantity_formula'] ?? null,
                'waste_factor_percent' => $line['waste_factor_percent'] ?? 0,
                'sort_order' => $line['sort_order'] ?? $index,
                'is_active' => $line['is_active'] ?? true,
                'notes' => $line['notes'] ?? null,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function validateLines(
        int $companyId,
        int $branchId,
        int $finishedItemId,
        array $lines,
        ?int $excludeBomId = null,
    ): void {
        if (count($lines) < 1) {
            throw ValidationException::withMessages([
                'lines' => __('BOM must include at least one raw material line.'),
            ]);
        }

        $finished = InventoryItem::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->find($finishedItemId);

        if ($finished === null) {
            throw ValidationException::withMessages([
                'finished_item_id' => __('Finished product not found for this branch.'),
            ]);
        }

        $duplicateQuery = ProductBom::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('finished_item_id', $finishedItemId);

        if ($excludeBomId !== null) {
            $duplicateQuery->where('id', '!=', $excludeBomId);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'finished_item_id' => __('A BOM already exists for this finished product.'),
            ]);
        }

        $componentIds = [];
        foreach ($lines as $index => $line) {
            $componentId = (int) $line['inventory_item_id'];

            if ($componentId === $finishedItemId) {
                throw ValidationException::withMessages([
                    "lines.{$index}.inventory_item_id" => __('Finished product cannot be its own component.'),
                ]);
            }

            if (in_array($componentId, $componentIds, true)) {
                throw ValidationException::withMessages([
                    "lines.{$index}.inventory_item_id" => __('Duplicate raw material in BOM.'),
                ]);
            }

            $componentIds[] = $componentId;

            if ((float) ($line['quantity_per_unit'] ?? 0) <= 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.quantity_per_unit" => __('Quantity per unit must be greater than zero.'),
                ]);
            }

            $component = InventoryItem::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->find($componentId);

            if ($component === null) {
                throw ValidationException::withMessages([
                    "lines.{$index}.inventory_item_id" => __('Raw material not found for this branch.'),
                ]);
            }
        }
    }

    /**
     * Sync catalog "required materials" to the product BOM.
     *
     * @param  list<array<string, mixed>>  $lines
     */
    public function syncFromCatalogItem(InventoryItem $item, array $lines, int $userId): ?ProductBom
    {
        $lines = collect($lines)
            ->filter(fn (array $line) => ! empty($line['inventory_item_id']))
            ->values()
            ->all();

        if ($lines === []) {
            ProductBom::query()
                ->where('company_id', $item->company_id)
                ->where('branch_id', $item->branch_id)
                ->where('finished_item_id', $item->id)
                ->update(['is_active' => false]);

            return null;
        }

        $bom = $this->findActiveForFinishedItem($item->company_id, $item->branch_id, $item->id);

        $normalized = collect($lines)->map(function (array $line, int $index) {
            $formula = trim((string) ($line['quantity_formula'] ?? ''));
            $perUnit = (float) ($line['quantity_per_unit'] ?? 1);

            return [
                'inventory_item_id' => (int) $line['inventory_item_id'],
                'quantity_per_unit' => $perUnit,
                'quantity_formula' => $formula !== '' ? $formula : null,
                'waste_factor_percent' => (float) ($line['waste_factor_percent'] ?? 0),
                'is_active' => (bool) ($line['is_active'] ?? true),
                'sort_order' => $index + 1,
            ];
        })->all();

        if ($bom === null) {
            return $this->create($item->company_id, $item->branch_id, $userId, [
                'finished_item_id' => $item->id,
                'name' => $item->item_name.' BOM',
                'is_active' => true,
            ], $normalized);
        }

        return $this->update($bom, [
            'finished_item_id' => $item->id,
            'name' => $bom->name,
            'is_active' => true,
        ], $normalized);
    }

    /**
     * @return Collection<int, array{line: ProductBomLine, required_quantity: float}>
     */
    public function requirementsForQuantity(ProductBom $bom, float $jobQuantity): Collection
    {
        $formulaService = app(MaterialQuantityFormulaService::class);

        return $bom->lines
            ->filter(fn (ProductBomLine $line) => (bool) ($line->is_active ?? true))
            ->map(function (ProductBomLine $line) use ($jobQuantity, $formulaService) {
                $base = $formulaService->evaluate(
                    $line->quantity_formula,
                    $jobQuantity,
                    (float) $line->quantity_per_unit,
                );
                $waste = (float) $line->waste_factor_percent;
                $required = round($base * (1 + ($waste / 100)), 3);

                return [
                    'line' => $line,
                    'required_quantity' => $required,
                ];
            });
    }
}
