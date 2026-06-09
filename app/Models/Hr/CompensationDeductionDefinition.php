<?php

namespace App\Models\Hr;

use App\Enums\PayrollComponentCalculationType;
use App\Enums\PayrollComponentFrequency;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'code',
    'name',
    'category',
    'calculation_type',
    'frequency',
    'default_amount',
    'percentage_rate',
    'is_active',
])]
class CompensationDeductionDefinition extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'calculation_type' => PayrollComponentCalculationType::class,
            'frequency' => PayrollComponentFrequency::class,
            'default_amount' => 'decimal:2',
            'percentage_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
