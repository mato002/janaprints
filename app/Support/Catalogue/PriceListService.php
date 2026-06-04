<?php

namespace App\Support\Catalogue;

use App\Models\Inventory\PriceList;

class PriceListService
{
    /**
     * @param array<int, array{inventory_item_id?: mixed, price_override?: mixed}> $lines
     */
    public function syncItems(PriceList $priceList, array $lines): void
    {
        $keep = [];

        foreach ($lines as $line) {
            if (blank($line['inventory_item_id'] ?? null) || blank($line['price_override'] ?? null)) {
                continue;
            }

            $record = $priceList->items()->updateOrCreate(
                ['inventory_item_id' => (int) $line['inventory_item_id']],
                ['price_override' => (float) $line['price_override']],
            );

            $keep[] = $record->id;
        }

        if ($keep !== []) {
            $priceList->items()->whereNotIn('id', $keep)->delete();
        }
    }
}
