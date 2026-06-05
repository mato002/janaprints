<?php

namespace App\Models\Governance;

use App\Enums\ApprovalChainStepStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalChainStepRecord extends Model
{
    protected $fillable = [
        'approval_chain_run_id',
        'approval_chain_step_id',
        'step_number',
        'status',
        'acted_by_user_id',
        'acted_at',
        'notes',
        'reminder_sent_at',
        'escalated_at',
        'escalated_to_role',
        'workflow_escalation_rule_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalChainStepStatus::class,
            'acted_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ApprovalChainRun::class, 'approval_chain_run_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalChainStep::class, 'approval_chain_step_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }

    public function escalationRule(): BelongsTo
    {
        return $this->belongsTo(WorkflowEscalationRule::class, 'workflow_escalation_rule_id');
    }
}
