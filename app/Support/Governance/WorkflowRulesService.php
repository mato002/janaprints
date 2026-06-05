<?php

namespace App\Support\Governance;

use App\Enums\WorkflowRuleExecutionStatus;
use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Governance\WorkflowRule;
use App\Models\Governance\WorkflowRuleExecution;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkflowRulesService
{
    public function __construct(
        protected WorkflowRuleEngine $engine,
        protected WorkflowRuleActionExecutor $executor,
    ) {}

    /**
     * @return Collection<int, WorkflowRuleExecution>
     */
    public function dispatch(
        WorkflowRuleTrigger|string $trigger,
        Model $subject,
        ?User $actor = null,
    ): Collection {
        $trigger = $trigger instanceof WorkflowRuleTrigger
            ? $trigger
            : WorkflowRuleTrigger::from((string) $trigger);

        $entityType = $this->entityKeyForModel($subject);
        $companyId = (int) $subject->company_id;
        $branchId = $subject->branch_id ?? null;

        $rules = $this->resolveRules($companyId, $branchId, $entityType, $trigger);
        $executions = collect();

        foreach ($rules as $rule) {
            if (! $this->engine->matchesConditions($rule->conditions_json, $subject)) {
                $executions->push($this->recordExecution(
                    $rule,
                    null,
                    $subject,
                    $trigger,
                    WorkflowRuleExecutionStatus::Skipped,
                    ['reason' => 'conditions_not_met'],
                ));

                continue;
            }

            foreach ($rule->actions as $action) {
                try {
                    $result = $this->executor->execute($action, $subject, $actor);
                    $executions->push($this->recordExecution(
                        $rule,
                        $action,
                        $subject,
                        $trigger,
                        WorkflowRuleExecutionStatus::Completed,
                        $result,
                    ));
                } catch (ValidationException $exception) {
                    $executions->push($this->recordExecution(
                        $rule,
                        $action,
                        $subject,
                        $trigger,
                        WorkflowRuleExecutionStatus::Failed,
                        null,
                        (string) collect($exception->errors())->flatten()->first(),
                    ));
                } catch (\Throwable $exception) {
                    $executions->push($this->recordExecution(
                        $rule,
                        $action,
                        $subject,
                        $trigger,
                        WorkflowRuleExecutionStatus::Failed,
                        null,
                        Str::limit($exception->getMessage(), 480),
                    ));
                }
            }
        }

        return $executions;
    }

    /**
     * @return Collection<int, WorkflowRule>
     */
    public function resolveRules(
        int $companyId,
        ?int $branchId,
        string $entityType,
        WorkflowRuleTrigger $trigger,
    ): Collection {
        $branchRules = $branchId
            ? $this->activeRulesQuery($companyId, $branchId, $entityType, $trigger)->get()
            : collect();

        if ($branchRules->isNotEmpty()) {
            return $branchRules;
        }

        return $this->activeRulesQuery($companyId, null, $entityType, $trigger)->get();
    }

    public function entityKeyForModel(Model $subject): string
    {
        $basename = Str::snake(class_basename($subject));

        foreach (config('workflow_rule_registry.entities', []) as $key => $meta) {
            if (($meta['model'] ?? null) === $subject::class) {
                return $key;
            }
        }

        return $basename;
    }

    /**
     * @return Collection<int, WorkflowRuleExecution>
     */
    public function recentExecutions(int $companyId, ?int $branchId, int $limit = 10): Collection
    {
        return WorkflowRuleExecution::query()
            ->whereHas('rule', function ($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);

                if ($branchId) {
                    $query->where(function ($inner) use ($branchId) {
                        $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
            })
            ->with(['rule', 'action'])
            ->orderByDesc('executed_at')
            ->limit($limit)
            ->get();
    }

    protected function activeRulesQuery(
        int $companyId,
        ?int $branchId,
        string $entityType,
        WorkflowRuleTrigger $trigger,
    ) {
        return WorkflowRule::query()
            ->where('company_id', $companyId)
            ->where('entity_type', $entityType)
            ->where('trigger', $trigger)
            ->where('status', WorkflowRuleStatus::Active)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with(['actions' => fn ($query) => $query->orderBy('sort_order')]);
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    protected function recordExecution(
        WorkflowRule $rule,
        ?\App\Models\Governance\WorkflowRuleAction $action,
        Model $subject,
        WorkflowRuleTrigger $trigger,
        WorkflowRuleExecutionStatus $status,
        ?array $result = null,
        ?string $errorMessage = null,
    ): WorkflowRuleExecution {
        return WorkflowRuleExecution::query()->create([
            'workflow_rule_id' => $rule->id,
            'workflow_rule_action_id' => $action?->id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'trigger' => $trigger,
            'status' => $status,
            'result_json' => $result,
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }
}
