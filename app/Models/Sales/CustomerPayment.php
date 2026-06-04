<?php

namespace App\Models\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPayment extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'payment_number',
        'payment_date',
        'payment_method',
        'is_deposit',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'currency',
        'status',
        'reference',
        'bank_reference',
        'mpesa_reference',
        'notes',
        'posted_by',
        'posted_at',
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
            'posted_at' => 'datetime',
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
}
