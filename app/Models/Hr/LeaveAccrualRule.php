<?php

namespace App\Models\Hr;

use App\Enums\LeaveAccrualFrequency;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'leave_type_id',
    'leave_policy_id',
    'frequency',
    'rate_per_period',
    'custom_interval_days',
    'effective_from',
    'is_active',
])]
class LeaveAccrualRule extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'frequency' => LeaveAccrualFrequency::class,
            'rate_per_period' => 'decimal:2',
            'custom_interval_days' => 'integer',
            'effective_from' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }
}
