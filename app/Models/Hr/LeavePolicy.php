<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'leave_type_id',
    'code',
    'name',
    'description',
    'min_notice_days',
    'max_consecutive_days',
    'requires_documentation',
    'is_active',
])]
class LeavePolicy extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'min_notice_days' => 'integer',
            'max_consecutive_days' => 'decimal:1',
            'requires_documentation' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function accrualRules(): HasMany
    {
        return $this->hasMany(LeaveAccrualRule::class);
    }

    public function carryForwardRules(): HasMany
    {
        return $this->hasMany(LeaveCarryForwardRule::class);
    }
}
