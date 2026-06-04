<?php

namespace App\Support\Catalogue;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ItemAttribute;
use Illuminate\Validation\ValidationException;

class ItemAttributeService
{
    /**
     * @param array<int|string, mixed> $values
     */
    public function sync(InventoryItem $item, array $values): void
    {
        $attributes = ItemAttribute::query()
            ->forTenant()
            ->where(function ($query) use ($item) {
                $query->whereNull('inventory_category_id')
                    ->orWhere('inventory_category_id', $item->inventory_category_id);
            })
            ->where('is_active', true)
            ->get();

        foreach ($attributes as $attribute) {
            $value = $values[$attribute->id] ?? null;

            if ($attribute->is_required && blank($value)) {
                throw ValidationException::withMessages([
                    "attributes.{$attribute->id}" => __(':attribute is required.', ['attribute' => $attribute->name]),
                ]);
            }

            if (blank($value)) {
                $item->attributeValues()->where('item_attribute_id', $attribute->id)->delete();

                continue;
            }

            $item->attributeValues()->updateOrCreate(
                ['item_attribute_id' => $attribute->id],
                [
                    'attribute_option_id' => $attribute->data_type === 'select' ? (int) $value : null,
                    'value' => $attribute->data_type === 'select' ? null : (string) $value,
                ],
            );
        }
    }
}
