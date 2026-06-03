<?php

namespace App\Models\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssue extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'to_warehouse_id', 'issue_number',
        'destination', 'issue_date', 'status', 'notes', 'issued_by', 'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'destination' => StockIssueDestination::class,
            'status' => InventoryDocumentStatus::class,
            'issue_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }
}
