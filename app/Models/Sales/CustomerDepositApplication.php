<?php

namespace App\Models\Sales;

use App\Enums\CustomerDepositApplicationStatus;
use App\Models\Branch;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDepositApplication extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'source_branch_id',
        'target_branch_id',
        'is_cross_branch',
        'override_reason',
        'customer_id',
        'customer_payment_id',
        'customer_invoice_id',
        'application_number',
        'application_date',
        'amount',
        'status',
        'notes',
        'posted_by',
        'posted_at',
        'posted_journal_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerDepositApplicationStatus::class,
            'application_date' => 'date',
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
            'is_cross_branch' => 'boolean',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
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

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function targetBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'target_branch_id');
    }
}
