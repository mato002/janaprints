<?php

namespace App\Models\Governance;

use App\Enums\WorkflowRuleExecutionStatus;
use App\Enums\WorkflowRuleTrigger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowRuleExecution extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'workflow_rule_id',
        'workflow_rule_action_id',
        'subject_type',
        'subject_id',
        'trigger',
        'status',
        'result_json',
        'error_message',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => WorkflowRuleTrigger::class,
            'status' => WorkflowRuleExecutionStatus::class,
            'result_json' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class, 'workflow_rule_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(WorkflowRuleAction::class, 'workflow_rule_action_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
