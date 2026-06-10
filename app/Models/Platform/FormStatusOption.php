<?php

namespace App\Models\Platform;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormStatusOption extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'form_key',
        'value',
        'label',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
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
}
