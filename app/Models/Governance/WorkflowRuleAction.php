<?php

namespace App\Models\Governance;

use App\Enums\WorkflowRuleActionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRuleAction extends Model
{
    protected $fillable = [
        'workflow_rule_id',
        'sort_order',
        'action_type',
        'config_json',
    ];

    protected function casts(): array
    {
        return [
            'action_type' => WorkflowRuleActionType::class,
            'config_json' => 'array',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class, 'workflow_rule_id');
    }
}
