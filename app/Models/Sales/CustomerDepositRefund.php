<?php

namespace App\Models\Sales;

use App\Enums\CustomerDepositRefundStatus;
use App\Enums\CustomerPaymentMethod;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDepositRefund extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'customer_payment_id',
        'refund_number',
        'refund_date',
        'payment_method',
        'amount',
        'status',
        'reference',
        'notes',
        'posted_by',
        'posted_at',
        'posted_journal_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerDepositRefundStatus::class,
            'payment_method' => CustomerPaymentMethod::class,
            'refund_date' => 'date',
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function depositPayment(): BelongsTo
    {
        return $this->belongsTo(CustomerPayment::class, 'customer_payment_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
