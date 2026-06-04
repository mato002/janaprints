<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItemAttribute extends Model
{
    protected $fillable = [
        'inventory_item_id', 'item_attribute_id', 'attribute_option_id', 'value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class, 'attribute_option_id');
    }
}
