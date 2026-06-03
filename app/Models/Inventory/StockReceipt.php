<?php

namespace App\Models\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'receipt_number', 'source',
        'receipt_date', 'status', 'notes', 'received_by', 'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => StockReceiptSource::class,
            'status' => InventoryDocumentStatus::class,
            'receipt_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }
}
