<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'basic_salary',
    'house_allowance',
    'transport_allowance',
    'medical_allowance',
    'effective_from',
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
            'effective_from' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function grossComponents(): float
    {
        return round(
            (float) $this->basic_salary
            + (float) $this->house_allowance
            + (float) $this->transport_allowance
            + (float) $this->medical_allowance,
            2,
        );
    }
}
