<?php

namespace App\Support\Catalogue;

use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventorySubcategory;
use Illuminate\Support\Str;

class CatalogueService
{
    public function structuredSku(
        InventoryCategory $category,
        ?InventorySubcategory $subcategory,
        ?string $brandName,
        string $itemName,
        array $attributes = [],
    ): string {
        $brandPart = null;

        if (filled($brandName)) {
            $normalized = strtoupper(trim($brandName));

            if (! in_array($normalized, ['GENERIC', 'GENERIC / NONE', 'NONE'], true)) {
                $brandPart = $brandName;
            }
        }

        $parts = [
            $category->code,
            $subcategory?->code,
            $brandPart,
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
