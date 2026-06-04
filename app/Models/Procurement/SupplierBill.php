<?php

namespace App\Models\Procurement;

use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Enums\SupplierPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierBill extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'vendor_id',
        'purchase_order_id',
        'goods_receipt_id',
        'credited_bill_id',
        'bill_number',
        'bill_type',
        'bill_date',
        'due_date',
        'currency',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
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
            'bill_type' => SupplierBillType::class,
            'status' => SupplierBillStatus::class,
            'bill_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function creditedBill(): BelongsTo
    {
        return $this->belongsTo(self::class, 'credited_bill_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(self::class, 'credited_bill_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierBillLine::class)->orderBy('sort_order');
    }

    public function taxLines(): HasMany
    {
        return $this->hasMany(SupplierBillTaxLine::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function refreshPaymentBalance(): void
    {
        $paid = (float) $this->paymentAllocations()
            ->whereHas('payment', fn ($q) => $q->where('status', SupplierPaymentStatus::Posted))
            ->sum('amount');

        $balance = round(max(0, (float) $this->total_amount - $paid), 2);

        $updates = [
            'amount_paid' => round($paid, 2),
            'balance_due' => $balance,
        ];

        if ($this->status === SupplierBillStatus::Posted && $balance <= 0.005) {
            $updates['status'] = SupplierBillStatus::Paid;
        }

        $this->update($updates);
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
}
