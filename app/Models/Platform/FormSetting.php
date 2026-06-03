<?php

namespace App\Models\Platform;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSetting extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'form_key',
        'is_active',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormFieldSetting::class)->orderBy('sort_order');
    }
}
