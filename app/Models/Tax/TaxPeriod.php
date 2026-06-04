<?php

namespace App\Models\Tax;

use App\Enums\TaxPeriodStatus;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxPeriod extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => TaxPeriodStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(TaxReturn::class);
    }

    public function scopeForTenant($query)
    {
        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
