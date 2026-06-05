<?php

namespace App\Models\Inventory;

use App\Enums\InventoryReconciliationStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReconciliation extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'stock_count_id', 'reconciliation_number',
        'status', 'approved_by', 'approved_at', 'posted_by', 'posted_at',
        'stock_adjustment_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => InventoryReconciliationStatus::class,
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
        ];
    }

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }
}
