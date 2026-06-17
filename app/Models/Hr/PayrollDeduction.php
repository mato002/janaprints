<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'code',
    'name',
    'category',
    'amount',
    'calculation_type',
    'frequency',
    'percentage_rate',
    'deduction_definition_id',
    'is_active',
    'applied_at',
])]
class PayrollDeduction extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function resolvedAmount(float $grossBase = 0): float
    {
        if (($this->calculation_type ?? 'fixed') === 'percentage') {
            return round($grossBase * ((float) ($this->percentage_rate ?? 0) / 100), 2);
        }

        return round((float) $this->amount, 2);
    }
}
