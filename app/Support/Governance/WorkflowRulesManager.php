<?php

namespace App\Support\Governance;

use App\Enums\WorkflowRuleActionType;
use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Governance\WorkflowRule;
use App\Models\Governance\WorkflowRuleAction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowRulesManager
{
    /**
     * @return Collection<int, WorkflowRule>
     */
    public function rulesForScope(int $companyId, ?int $branchId): Collection
    {
        if ($branchId) {
            $branchRules = $this->scopedRulesQuery($companyId, $branchId)->get();

            if ($branchRules->isNotEmpty()) {
                return $branchRules;
            }
        }

        return $this->scopedRulesQuery($companyId, null)->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $actions
     */
    public function create(int $companyId, ?int $branchId, array $data, array $actions, User $actor): WorkflowRule
    {
        return DB::transaction(function () use ($companyId, $branchId, $data, $actions, $actor) {
            $rule = WorkflowRule::query()->create([
                ...$this->ruleAttributes($data),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'status' => WorkflowRuleStatus::Draft,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->syncActions($rule, $actions);

            return $rule->load('actions');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $actions
     */
    public function update(WorkflowRule $rule, array $data, array $actions, User $actor): WorkflowRule
    {
        return DB::transaction(function () use ($rule, $data, $actions, $actor) {
            $rule->update([
                ...$this->ruleAttributes($data),
                'updated_by' => $actor->id,
            ]);

            $this->syncActions($rule, $actions);

            return $rule->fresh(['actions']);
        });
    }

    public function activate(WorkflowRule $rule, User $actor): WorkflowRule
    {
        if ($rule->actions()->count() === 0) {
            throw ValidationException::withMessages([
                'actions' => __('A workflow rule must have at least one action before activation.'),
            ]);
        }

        $rule->update([
            'status' => WorkflowRuleStatus::Active,
            'updated_by' => $actor->id,
        ]);

        return $rule->fresh(['actions']);
    }

    public function deactivate(WorkflowRule $rule, User $actor): WorkflowRule
    {
        $rule->update([
            'status' => WorkflowRuleStatus::Inactive,
            'updated_by' => $actor->id,
        ]);

        return $rule->fresh(['actions']);
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryMetrics(int $companyId, ?int $branchId): array
    {
        $rules = $this->rulesForScope($companyId, $branchId);

        return [
            'total' => $rules->count(),
            'active' => $rules->where('status', WorkflowRuleStatus::Active)->count(),
            'draft' => $rules->where('status', WorkflowRuleStatus::Draft)->count(),
            'inactive' => $rules->where('status', WorkflowRuleStatus::Inactive)->count(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function entityOptions(): array
    {
        return collect(config('workflow_rule_registry.entities', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => __($meta['label'])])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function triggerOptions(): array
    {
        return collect(WorkflowRuleTrigger::cases())
            ->mapWithKeys(fn (WorkflowRuleTrigger $trigger) => [$trigger->value => $trigger->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function actionTypeOptions(): array
    {
        return collect(WorkflowRuleActionType::cases())
            ->mapWithKeys(fn (WorkflowRuleActionType $type) => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function ruleAttributes(array $data): array
    {
        $entity = (string) $data['entity_type'];
        $module = config("workflow_rule_registry.entities.{$entity}.module", (string) ($data['module'] ?? 'system'));

        return [
            'name' => (string) $data['name'],
            'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
            'module' => $module,
            'entity_type' => $entity,
            'trigger' => (string) $data['trigger'],
            'conditions_json' => $this->normalizeConditions($data['conditions'] ?? []),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $conditions
     * @return list<array{field: string, operator: string, value: mixed}>
     */
    protected function normalizeConditions(array $conditions): array
    {
        return collect($conditions)
            ->filter(fn (array $condition) => filled($condition['field'] ?? null))
            ->map(fn (array $condition) => [
                'field' => (string) $condition['field'],
                'operator' => (string) ($condition['operator'] ?? 'equals'),
                'value' => $condition['value'] ?? null,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $actions
     */
    protected function syncActions(WorkflowRule $rule, array $actions): void
    {
        $rule->actions()->delete();

        foreach (array_values($actions) as $index => $action) {
            if (blank($action['action_type'] ?? null)) {
                continue;
            }

            WorkflowRuleAction::query()->create([
                'workflow_rule_id' => $rule->id,
                'sort_order' => $index + 1,
                'action_type' => (string) $action['action_type'],
                'config_json' => $this->normalizeActionConfig($action),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $action
     * @return array<string, mixed>
     */
    protected function normalizeActionConfig(array $action): array
    {
        $config = $action['config'] ?? [];

        if (! is_array($config)) {
            return [];
        }

        return collect($config)
            ->filter(fn ($value, $key) => filled($key) && filled($value))
            ->all();
    }

    protected function scopedRulesQuery(int $companyId, ?int $branchId)
    {
        return WorkflowRule::query()
            ->where('company_id', $companyId)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with('actions')
            ->orderBy('name');
    }
}
