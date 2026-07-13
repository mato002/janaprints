<?php

namespace App\Models\Communications;

use App\Models\Company;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsWalletTopup extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'requested_by',
        'reference',
        'provider_transaction_id',
        'checkout_request_id',
        'amount',
        'phone_number',
        'status',
        'mpesa_receipt',
        'provider_balance_after',
        'local_credit_applied',
        'message',
        'meta',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_balance_after' => 'decimal:2',
            'local_credit_applied' => 'boolean',
            'meta' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled', 'expired'], true);
    }
}
