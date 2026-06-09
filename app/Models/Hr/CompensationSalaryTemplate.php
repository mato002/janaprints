<?php

namespace App\Models\Hr;

use App\Enums\PaymentFrequency;
use App\Enums\PayrollGroup;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'code',
    'name',
    'basic_salary',
    'house_allowance',
    'transport_allowance',
    'medical_allowance',
    'risk_allowance',
    'responsibility_allowance',
    'payment_frequency',
    'payroll_group',
    'currency',
    'is_active',
])]
class CompensationSalaryTemplate extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'house_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'medical_allowance' => 'decimal:2',
            'risk_allowance' => 'decimal:2',
            'responsibility_allowance' => 'decimal:2',
            'payment_frequency' => PaymentFrequency::class,
            'payroll_group' => PayrollGroup::class,
            'is_active' => 'boolean',
        ];
    }

    public function grossComponents(): float
    {
        return round(
            (float) $this->basic_salary
            + (float) $this->house_allowance
            + (float) $this->transport_allowance
            + (float) $this->medical_allowance
            + (float) $this->risk_allowance
            + (float) $this->responsibility_allowance,
            2,
        );
    }
}
