<?php

namespace App\Models\Client;

use App\Enums\ClientPortalRepeatRequestStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPortalRepeatRequest extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sales_order_id',
        'requested_by',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientPortalRepeatRequestStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
