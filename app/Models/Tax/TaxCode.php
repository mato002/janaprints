<?php

namespace App\Models\Tax;

use App\Models\Concerns\LogsActivity;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxCode extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'tax_category_id',
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class, 'tax_category_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class)->orderByDesc('effective_from');
    }

    public function scopeForTenant($query)
    {
        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
