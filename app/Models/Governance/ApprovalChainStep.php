<?php

namespace App\Models\Governance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalChainStep extends Model
{
    protected $fillable = [
        'approval_chain_id',
        'step_number',
        'approver_role',
        'approver_user_id',
        'approval_limit',
        'is_required',
        'condition_json',
    ];

    protected function casts(): array
    {
        return [
            'approval_limit' => 'decimal:2',
            'is_required' => 'boolean',
            'condition_json' => 'array',
        ];
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function stepRecords(): HasMany
    {
        return $this->hasMany(ApprovalChainStepRecord::class);
    }

    public function approverLabel(): string
    {
        if ($this->approver_user_id && $this->approverUser) {
            return $this->approverUser->name;
        }

        return $this->approver_role ?? __('Unassigned');
    }
}
