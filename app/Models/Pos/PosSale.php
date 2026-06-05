<?php

namespace App\Models\Pos;

use App\Enums\PosSaleStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosSale extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'cashier_id', 'pos_session_id', 'customer_id', 'sale_number', 'sale_date',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'amount_paid', 'balance_due',
        'status', 'is_walk_in', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'status' => PosSaleStatus::class,
            'is_walk_in' => 'boolean',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
    }

    public function hold(): HasOne
    {
        return $this->hasOne(PosSaleHold::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PosReturn::class, 'pos_sale_id');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $sale = static::query()->forTenant()->where($field, $value)->first();

        if ($sale === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $sale;
    }
}
