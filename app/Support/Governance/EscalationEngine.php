<?php

namespace App\Support\Governance;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\EscalationEventType;
use App\Enums\EscalationMethod;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationEvent;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\User;
use App\Services\Security\SecurityAuditService;

class EscalationEngine
{
    public function __construct(
        protected EscalationsService $service,
        protected SecurityAuditService $auditService,
    ) {}

    /**
     * @return array{reminders: int, escalations: int, skipped: int}
     */
    public function processPendingSteps(?int $companyId = null): array
    {
        $stats = ['reminders' => 0, 'escalations' => 0, 'skipped' => 0];

        $query = ApprovalChainStepRecord::query()
            ->where('status', ApprovalChainStepStatus::Pending)
            ->whereHas('run', fn ($runQuery) => $runQuery
                ->where('status', ApprovalChainRunStatus::Pending)
                ->when($companyId, fn ($scoped) => $scoped->where('company_id', $companyId)))
            ->with(['run.chain', 'step']);

        foreach ($query->cursor() as $record) {
            $rule = $this->service->resolveRuleForStepRecord($record);

            if ($rule === null) {
                $stats['skipped']++;

                continue;
            }

            if (! $this->hasTimedOut($record, $rule)) {
                $stats['skipped']++;

                continue;
            }

            if ($rule->escalation_method === EscalationMethod::Reminder) {
                if ($this->processReminder($record, $rule)) {
                    $stats['reminders']++;
                } else {
                    $stats['skipped']++;
                }

                continue;
            }

            if ($this->processAutoEscalation($record, $rule)) {
                $stats['escalations']++;
            } else {
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    public function hasTimedOut(ApprovalChainStepRecord $record, WorkflowEscalationRule $rule): bool
    {
        $waitingSince = $record->created_at ?? now();

        return now()->gte($waitingSince->copy()->addHours($rule->waiting_hours));
    }

    public function processReminder(ApprovalChainStepRecord $record, WorkflowEscalationRule $rule): bool
    {
        if ($record->reminder_sent_at !== null) {
            return false;
        }

        $record->update([
            'reminder_sent_at' => now(),
            'workflow_escalation_rule_id' => $rule->id,
        ]);

        $this->recordEvent($record, $rule, EscalationEventType::ReminderSent);
        $this->auditEscalationAction('escalation.reminder_sent', $record, $rule);

        return true;
    }

    public function processAutoEscalation(ApprovalChainStepRecord $record, WorkflowEscalationRule $rule): bool
    {
        if ($record->status === ApprovalChainStepStatus::Escalated) {
            return false;
        }

        $record->update([
            'status' => ApprovalChainStepStatus::Escalated,
            'escalated_at' => now(),
            'escalated_to_role' => $rule->escalation_target_role,
            'workflow_escalation_rule_id' => $rule->id,
        ]);

        $this->recordEvent($record, $rule, EscalationEventType::Escalated, [
            'escalation_target_role' => $rule->escalation_target_role,
        ]);
        $this->auditEscalationAction('escalation.auto_escalated', $record, $rule);

        return true;
    }

    public function canUserActOnEscalatedStep(User $user, ApprovalChainStepRecord $record): bool
    {
        if ($record->status !== ApprovalChainStepStatus::Escalated) {
            return false;
        }

        if ($record->escalated_to_role && $user->hasRole($record->escalated_to_role)) {
            return true;
        }

        return false;
    }

    protected function recordEvent(
        ApprovalChainStepRecord $record,
        WorkflowEscalationRule $rule,
        EscalationEventType $type,
        array $metadata = [],
    ): void {
        WorkflowEscalationEvent::query()->create([
            'workflow_escalation_rule_id' => $rule->id,
            'approval_chain_step_record_id' => $record->id,
            'event_type' => $type,
            'metadata' => $metadata === [] ? null : $metadata,
            'created_at' => now(),
        ]);
    }

    protected function auditEscalationAction(
        string $action,
        ApprovalChainStepRecord $record,
        WorkflowEscalationRule $rule,
    ): void {
        $record->loadMissing('run');

        $this->auditService->record(
            action: $action,
            subject: $record,
            after: [
                'rule_id' => $rule->id,
                'workflow_key' => $rule->workflow_key,
                'run_id' => $record->approval_chain_run_id,
                'escalation_target_role' => $rule->escalation_target_role,
            ],
            module: 'governance',
            entity: 'workflow_escalation',
        );
    }
}
