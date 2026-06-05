<?php

namespace App\Models\Platform;

use App\Enums\DelegationReason;
use App\Enums\DelegationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalDelegation extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'delegator_user_id',
        'delegate_user_id',
        'modules',
        'approval_types',
        'reason',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by_user_id',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'approval_types' => 'array',
            'reason' => DelegationReason::class,
            'status' => DelegationStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function coversModule(string $module): bool
    {
        $modules = $this->modules ?? [];

        return $modules === [] || in_array($module, $modules, true);
    }

    public function coversApprovalType(string $approvalType): bool
    {
        $types = $this->approval_types ?? [];

        return $types === [] || in_array($approvalType, $types, true);
    }
}
