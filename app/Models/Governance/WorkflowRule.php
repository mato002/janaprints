<?php

namespace App\Models\Governance;

use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRule extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'description',
        'module',
        'entity_type',
        'trigger',
        'conditions_json',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => WorkflowRuleTrigger::class,
            'status' => WorkflowRuleStatus::class,
            'conditions_json' => 'array',
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

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowRuleAction::class)->orderBy('sort_order');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowRuleExecution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
