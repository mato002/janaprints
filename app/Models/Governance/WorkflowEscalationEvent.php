<?php

namespace App\Models\Governance;

use App\Enums\EscalationEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowEscalationEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'workflow_escalation_rule_id',
        'approval_chain_step_record_id',
        'event_type',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EscalationEventType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WorkflowEscalationRule::class, 'workflow_escalation_rule_id');
    }

    public function stepRecord(): BelongsTo
    {
        return $this->belongsTo(ApprovalChainStepRecord::class, 'approval_chain_step_record_id');
    }
}
