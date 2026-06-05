<?php

namespace App\Models\Inventory;

use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockCount extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'cycle_count_schedule_id',
        'count_number', 'count_type', 'count_date', 'status', 'notes',
        'created_by', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at',
        'posted_by', 'posted_at', 'stock_adjustment_id',
    ];

    protected function casts(): array
    {
        return [
            'count_type' => StockCountType::class,
            'status' => StockCountStatus::class,
            'count_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cycleCountSchedule(): BelongsTo
    {
        return $this->belongsTo(CycleCountSchedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
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

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function reconciliation(): HasOne
    {
        return $this->hasOne(InventoryReconciliation::class);
    }
}
