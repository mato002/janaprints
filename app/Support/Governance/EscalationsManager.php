<?php

namespace App\Support\Governance;

use App\Enums\EscalationRuleStatus;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Illuminate\Support\Facades\DB;

class EscalationsManager
{
    public function __construct(
        protected EscalationsService $service,
        protected SecurityAuditService $auditService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function dashboardRows(int $companyId, ?int $branchId): array
    {
        return $this->service->rulesForScope($companyId, $branchId)
            ->map(fn (WorkflowEscalationRule $rule) => $this->presentRow($rule))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(WorkflowEscalationRule $rule): array
    {
        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'workflow_key' => $rule->workflow_key,
            'workflow' => $rule->workflowLabel(),
            'waiting_hours' => $rule->waiting_hours,
            'waiting_period' => $rule->waitingPeriodLabel(),
            'escalation_target_role' => $rule->escalation_target_role,
            'escalation_method' => $rule->escalation_method->label(),
            'escalation_method_key' => $rule->escalation_method->value,
            'status' => $rule->status->label(),
            'status_key' => $rule->status->value,
            'is_operational' => $rule->status->isOperational(),
            'description' => $rule->description,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, ?int $branchId, array $data, User $actor): WorkflowEscalationRule
    {
        return DB::transaction(function () use ($companyId, $branchId, $data, $actor) {
            $rule = WorkflowEscalationRule::query()->create([
                ...$this->ruleAttributes($data),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'status' => EscalationRuleStatus::Draft,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditService->record(
                action: 'escalation.created',
                subject: $rule,
                after: $this->auditSnapshot($rule),
                module: 'governance',
                entity: 'workflow_escalation_rule',
            );

            return $rule;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WorkflowEscalationRule $rule, array $data, User $actor): WorkflowEscalationRule
    {
        return DB::transaction(function () use ($rule, $data, $actor) {
            $before = $this->auditSnapshot($rule);

            $rule->update([
                ...$this->ruleAttributes($data),
                'updated_by' => $actor->id,
            ]);

            $this->auditService->record(
                action: 'escalation.updated',
                subject: $rule,
                before: $before,
                after: $this->auditSnapshot($rule->fresh()),
                module: 'governance',
                entity: 'workflow_escalation_rule',
            );

            return $rule->fresh();
        });
    }

    public function activate(WorkflowEscalationRule $rule, User $actor): WorkflowEscalationRule
    {
        WorkflowEscalationRule::query()
            ->where('company_id', $rule->company_id)
            ->where('branch_id', $rule->branch_id)
            ->where('workflow_key', $rule->workflow_key)
            ->where('id', '!=', $rule->id)
            ->where('status', EscalationRuleStatus::Active)
            ->update(['status' => EscalationRuleStatus::Inactive]);

        $rule->update([
            'status' => EscalationRuleStatus::Active,
            'updated_by' => $actor->id,
        ]);

        $this->auditService->record(
            action: 'escalation.activated',
            subject: $rule,
            after: $this->auditSnapshot($rule->fresh()),
            module: 'governance',
            entity: 'workflow_escalation_rule',
        );

        return $rule->fresh();
    }

    public function deactivate(WorkflowEscalationRule $rule, User $actor): WorkflowEscalationRule
    {
        $rule->update([
            'status' => EscalationRuleStatus::Inactive,
            'updated_by' => $actor->id,
        ]);

        $this->auditService->record(
            action: 'escalation.deactivated',
            subject: $rule,
            after: $this->auditSnapshot($rule->fresh()),
            module: 'governance',
            entity: 'workflow_escalation_rule',
        );

        return $rule->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function ruleAttributes(array $data): array
    {
        return [
            'name' => (string) $data['name'],
            'workflow_key' => (string) $data['workflow_key'],
            'waiting_hours' => (int) $data['waiting_hours'],
            'escalation_target_role' => (string) $data['escalation_target_role'],
            'escalation_method' => (string) $data['escalation_method'],
            'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function auditSnapshot(WorkflowEscalationRule $rule): array
    {
        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'workflow_key' => $rule->workflow_key,
            'waiting_hours' => $rule->waiting_hours,
            'escalation_target_role' => $rule->escalation_target_role,
            'escalation_method' => $rule->escalation_method->value,
            'status' => $rule->status->value,
        ];
    }
}
