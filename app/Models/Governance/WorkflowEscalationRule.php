<?php

namespace App\Models\Governance;

use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowEscalationRule extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'workflow_key',
        'waiting_hours',
        'escalation_target_role',
        'escalation_method',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'waiting_hours' => 'integer',
            'escalation_method' => EscalationMethod::class,
            'status' => EscalationRuleStatus::class,
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkflowEscalationEvent::class);
    }

    public function workflowLabel(): string
    {
        return (string) config("escalation_registry.workflows.{$this->workflow_key}.label", $this->workflow_key);
    }

    public function waitingPeriodLabel(): string
    {
        $presets = config('escalation_registry.waiting_period_presets', []);

        if (isset($presets[$this->waiting_hours])) {
            return __($presets[$this->waiting_hours]);
        }

        return trans_choice(':count hour|:count hours', $this->waiting_hours, ['count' => $this->waiting_hours]);
    }
}
