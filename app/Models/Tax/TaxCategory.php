<?php

namespace App\Models\Tax;

use App\Enums\TaxCategoryType;
use App\Enums\TaxDirection;
use App\Models\Concerns\LogsActivity;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxCategory extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'direction',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => TaxCategoryType::class,
            'direction' => TaxDirection::class,
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function taxCodes(): HasMany
    {
        return $this->hasMany(TaxCode::class);
    }

    public function scopeForTenant($query)
    {
        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
