<?php

namespace App\Models\Communications;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCreditBalance extends Model
{
    protected $fillable = [
        'company_id', 'opening_credits', 'purchased_credits',
        'used_credits', 'remaining_credits', 'cost_per_sms',
    ];

    protected function casts(): array
    {
        return [
            'opening_credits' => 'decimal:2',
            'purchased_credits' => 'decimal:2',
            'used_credits' => 'decimal:2',
            'remaining_credits' => 'decimal:2',
            'cost_per_sms' => 'decimal:4',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
