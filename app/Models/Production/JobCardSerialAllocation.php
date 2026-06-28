<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCardSerialAllocation extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'inventory_item_id',
        'serial_prefix', 'serial_padding_length',
        'serial_start', 'serial_end', 'produced_end',
        'spoiled_quantity', 'is_confirmed', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'serial_padding_length' => 'integer',
            'serial_start' => 'integer',
            'serial_end' => 'integer',
            'produced_end' => 'integer',
            'spoiled_quantity' => 'integer',
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
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

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function spoiledRanges(): HasMany
    {
        return $this->hasMany(JobCardSpoiledSerialRange::class, 'production_job_card_id', 'production_job_card_id');
    }

    public function formatSerial(int $number): string
    {
        $padded = str_pad((string) $number, $this->serial_padding_length, '0', STR_PAD_LEFT);

        return $this->serial_prefix.$padded;
    }

    public function allocatedQuantity(): int
    {
        return (int) ($this->serial_end - $this->serial_start + 1);
    }
}
