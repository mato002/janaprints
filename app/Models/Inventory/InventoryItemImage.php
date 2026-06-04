<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InventoryItemImage extends Model
{
    protected $fillable = [
        'inventory_item_id', 'path', 'thumbnail_path', 'is_primary', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function thumbnailUrl(): string
    {
        return Storage::disk('public')->url($this->thumbnail_path ?: $this->path);
    }
}
