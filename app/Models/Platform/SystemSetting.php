<?php

namespace App\Models\Platform;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'key',
        'value',
        'value_type',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_locked' => 'boolean',
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
