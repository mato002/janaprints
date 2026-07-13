<?php

namespace App\Models\Tax;

use App\Enums\TaxReturnStatus;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReturn extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'tax_period_id',
        'return_number',
        'return_type',
        'status',
        'output_tax',
        'input_tax',
        'withholding_tax',
        'net_liability',
        'filed_by',
        'filed_at',
        'filing_package_path',
        'filing_package_checksum',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaxReturnStatus::class,
            'output_tax' => 'decimal:2',
            'input_tax' => 'decimal:2',
            'withholding_tax' => 'decimal:2',
            'net_liability' => 'decimal:2',
            'filed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function taxPeriod(): BelongsTo
    {
        return $this->belongsTo(TaxPeriod::class);
    }

    public function filedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function scopeForTenant($query)
    {
        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
