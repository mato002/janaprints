<?php

namespace App\Models\Tax;

use App\Enums\TaxDirection;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'tax_code_id',
        'tax_category_id',
        'tax_period_id',
        'direction',
        'source_type',
        'source_id',
        'document_number',
        'document_date',
        'taxable_amount',
        'tax_amount',
        'rate_percent',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'direction' => TaxDirection::class,
            'document_date' => 'date',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'rate_percent' => 'decimal:4',
            'posted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    public function taxCategory(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class);
    }

    public function taxPeriod(): BelongsTo
    {
        return $this->belongsTo(TaxPeriod::class);
    }

    public function scopeForTenant($query)
    {
        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
