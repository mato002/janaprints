<?php

namespace App\Models\Inventory;

use App\Enums\CycleCountFrequency;
use App\Enums\CycleCountScheduleStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CycleCountSchedule extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'inventory_category_id',
        'frequency', 'next_count_date', 'responsible_user_id', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => CycleCountFrequency::class,
            'status' => CycleCountScheduleStatus::class,
            'next_count_date' => 'date',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(StockCount::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === CycleCountScheduleStatus::Active
            && $this->next_count_date->isPast();
    }
}
