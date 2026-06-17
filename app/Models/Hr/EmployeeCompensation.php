<?php

namespace App\Models\Hr;

use App\Enums\CompensationStatus;
use App\Enums\PaymentFrequency;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use App\Models\User;
use App\Support\Hr\PayrollGroupService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'basic_salary',
    'house_allowance',
    'transport_allowance',
    'medical_allowance',
    'risk_allowance',
    'responsibility_allowance',
    'effective_from',
    'payment_frequency',
    'payroll_group',
    'currency',
    'status',
    'change_reason',
    'changed_by_user_id',
    'salary_template_id',
    'is_active',
])]
class EmployeeCompensation extends Model
{
    use BelongsToCompany, LogsActivity;

    protected $table = 'employee_compensations';

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'house_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'medical_allowance' => 'decimal:2',
            'risk_allowance' => 'decimal:2',
            'responsibility_allowance' => 'decimal:2',
            'effective_from' => 'date',
            'payment_frequency' => PaymentFrequency::class,
            'status' => CompensationStatus::class,
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function salaryTemplate(): BelongsTo
    {
        return $this->belongsTo(CompensationSalaryTemplate::class, 'salary_template_id');
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
