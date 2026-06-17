<?php

namespace App\Models\Hr;

use App\Enums\PaymentFrequency;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Support\Hr\PayrollGroupService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'is_active' => 'boolean',
        ];
    }

    protected function payrollGroupLabel(): Attribute
    {
        return Attribute::get(fn () => app(PayrollGroupService::class)->label(
            (int) $this->company_id,
            $this->payroll_group,
        ));
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

    public function employeeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class, 'salary_template_id');
    }
}
