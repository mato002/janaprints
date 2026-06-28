<?php

namespace App\Models\Production;

use App\Models\Inventory\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardSpoiledSerialRange extends Model
{
    protected $fillable = [
        'production_job_card_id', 'inventory_item_id',
        'serial_start', 'serial_end', 'quantity',
        'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'serial_start' => 'integer',
            'serial_end' => 'integer',
            'quantity' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
