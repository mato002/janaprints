<?php

namespace App\Models;

use App\Enums\JobTitleLevel;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'code',
    'title',
    'department_id',
    'description',
    'level',
    'sort_order',
    'reports_to_job_title_id',
    'approval_authority',
    'is_active',
])]
class JobTitle extends Model
{
    use BelongsToCompany, HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'level' => JobTitleLevel::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_job_title_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reports_to_job_title_id')->orderBy('sort_order');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
