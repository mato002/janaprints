<?php

namespace App\Models\Pos;

use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosReturnType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosReturn extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'pos_sale_id', 'pos_session_id', 'created_by', 'approved_by',
        'return_number', 'return_type', 'status', 'refund_method', 'subtotal', 'tax_amount',
        'refund_amount', 'is_full_return', 'reason', 'rejection_reason', 'refund_reference',
        'approved_at', 'completed_at', 'rejected_at', 'notes', 'posted_journal_id',
    ];

    protected function casts(): array
    {
        return [
            'return_type' => PosReturnType::class,
            'status' => PosReturnStatus::class,
            'refund_method' => PosRefundMethod::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'is_full_return' => 'boolean',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosReturnItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PosReturnEvent::class)->orderBy('created_at');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $return = static::query()->forTenant()->where($field, $value)->first();

        if ($return === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $return;
    }
}
