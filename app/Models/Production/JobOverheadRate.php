<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JobOverheadRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'rate_name',
        'production_type',
        'rate_percent',
        'fixed_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_percent' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
