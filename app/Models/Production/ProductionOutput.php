<?php

namespace App\Models\Production;

use App\Enums\ProductionOutputStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOutput extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'production_job_card_id',
        'posted_job_marker',
        'finished_inventory_item_id',
        'finished_warehouse_id',
        'inventory_movement_id',
        'quantity_completed',
        'quantity_rejected',
        'unit_cost',
        'total_cost',
        'completion_status',
        'completed_by',
        'completed_at',
        'posted_journal_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity_completed' => 'decimal:3',
            'quantity_rejected' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'completion_status' => ProductionOutputStatus::class,
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function finishedItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'finished_inventory_item_id');
    }

    public function finishedWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'finished_warehouse_id');
    }

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }
}
