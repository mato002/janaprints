<?php

namespace App\Support\Catalogue;

use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventorySubcategory;
use Illuminate\Support\Str;

class CatalogueService
{
    public function structuredSku(
        InventoryCategory $category,
        ?InventorySubcategory $subcategory,
        ?Brand $brand,
        string $itemName,
        array $attributes = [],
    ): string {
        $parts = [
            $category->code,
            $subcategory?->code,
            $brand && strtoupper($brand->code) !== 'GENERIC' ? $brand->code : null,
            ...array_values(array_filter($attributes, fn ($value) => filled($value))),
        ];

        if (count(array_filter($parts)) < 2) {
            $parts[] = Str::before($itemName, ' ');
        }

        return collect($parts)
            ->filter()
            ->map(fn ($part) => Str::upper(Str::slug((string) $part, '-')))
            ->implode('-');
    }
}
