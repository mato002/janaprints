<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'payroll_run_id', 'employee_id', 'reference',
    'basic_salary', 'total_allowances', 'gross_pay',
    'paye', 'shif', 'nssf', 'housing_levy', 'other_deductions',
    'total_deductions', 'net_pay',
    'days_worked', 'leave_days', 'absent_days', 'emailed_at', 'released_at',
])]
class PayrollPayslip extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'paye' => 'decimal:2',
            'shif' => 'decimal:2',
            'nssf' => 'decimal:2',
            'housing_levy' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'emailed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollPayslipItem::class);
    }
}
