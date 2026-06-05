<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'branch_id',
    'department_id',
    'job_title_id',
    'employee_number',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'phone',
    'email',
    'national_id',
    'kra_pin',
    'nhif_number',
    'nssf_number',
    'designation',
    'hire_date',
    'employment_status',
    'photo',
    'is_active',
])]
class Employee extends Model
{
    use BelongsToCompany, HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'employment_status' => EmploymentStatus::class,
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]))));
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
