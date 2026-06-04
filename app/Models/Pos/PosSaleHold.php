<?php

namespace App\Models\Pos;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleHold extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'cashier_id', 'pos_sale_id', 'customer_id',
        'hold_label', 'held_at',
    ];

    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
