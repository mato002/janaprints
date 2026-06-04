<?php

namespace App\Models\Procurement;

use App\Enums\SupplierPaymentMethod;
use App\Enums\SupplierPaymentStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Tax\TaxCode;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayment extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'vendor_id',
        'withholding_tax_code_id',
        'withholding_tax_amount',
        'payment_number',
        'payment_date',
        'payment_method',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'currency',
        'status',
        'reference',
        'bank_reference',
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
            'payment_method' => SupplierPaymentMethod::class,
            'status' => SupplierPaymentStatus::class,
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'withholding_tax_amount' => 'decimal:2',
            'allocated_amount' => 'decimal:2',
            'unallocated_amount' => 'decimal:2',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function withholdingTaxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class, 'withholding_tax_code_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
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
