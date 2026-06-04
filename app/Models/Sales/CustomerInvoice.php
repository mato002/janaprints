<?php

namespace App\Models\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerInvoice extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sales_order_id',
        'production_job_card_id',
        'credited_invoice_id',
        'invoice_number',
        'invoice_type',
        'invoice_date',
        'due_date',
        'currency',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'billing_percent',
        'deposit_amount',
        'notes',
        'approved_by',
        'approved_at',
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
            'invoice_type' => CustomerInvoiceType::class,
            'status' => CustomerInvoiceStatus::class,
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'billing_percent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function creditedInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'credited_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(self::class, 'credited_invoice_id');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function refreshPaymentBalance(): void
    {
        $paid = (float) $this->paymentAllocations()
            ->whereHas('payment', fn ($q) => $q->where('status', CustomerPaymentStatus::Posted))
            ->sum('amount');

        $this->update([
            'amount_paid' => round($paid, 2),
            'balance_due' => round(max(0, (float) $this->total_amount - $paid), 2),
        ]);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CustomerInvoiceLine::class)->orderBy('sort_order');
    }

    public function taxLines(): HasMany
    {
        return $this->hasMany(CustomerInvoiceTaxLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }

    public function transitionTo(CustomerInvoiceStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException(
                "Cannot transition invoice from {$this->status->value} to {$status->value}",
            );
        }

        $this->update(['status' => $status]);
    }
}
