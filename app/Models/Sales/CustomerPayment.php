<?php

namespace App\Models\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\PublicHash\PublicHashResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPayment extends Model
{
    use BelongsToTenant, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'payment_number',
        'receipt_number',
        'payment_date',
        'payment_method',
        'is_deposit',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'credit_issued',
        'credit_remaining',
        'credit_applied',
        'credit_refunded',
        'currency',
        'status',
        'reference',
        'bank_reference',
        'mpesa_reference',
        'notes',
        'posted_by',
        'posted_at',
        'receipt_emailed_at',
        'receipt_sms_sent_at',
        'posted_journal_id',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => CustomerPaymentMethod::class,
            'status' => CustomerPaymentStatus::class,
            'payment_date' => 'date',
            'is_deposit' => 'boolean',
            'amount' => 'decimal:2',
            'allocated_amount' => 'decimal:2',
            'unallocated_amount' => 'decimal:2',
            'credit_issued' => 'decimal:2',
            'credit_remaining' => 'decimal:2',
            'credit_applied' => 'decimal:2',
            'credit_refunded' => 'decimal:2',
            'posted_at' => 'datetime',
            'receipt_emailed_at' => 'datetime',
            'receipt_sms_sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $resolver = app(PublicHashResolver::class);

        if (request()->routeIs('public.payment-receipt.show')) {
            $allowLegacyNumeric = request()->hasValidSignature()
                && config('public_hashes.signed_receipt_legacy_numeric_enabled', true);

            return $resolver->resolveForExternalWithLegacyNumeric(
                static::class,
                $value,
                $field,
                allowLegacyNumeric: $allowLegacyNumeric,
            );
        }

        return $resolver->resolve(static::class, $value, $field);
    }
}
