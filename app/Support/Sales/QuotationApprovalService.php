<?php

namespace App\Support\Sales;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\DomainCommunicationEvent;
use App\Enums\NotificationType;
use App\Enums\QuotationStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Governance\ApprovalChainEngine;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\WorkflowRulesService;
use App\Support\Platform\ApprovalDelegationService;
use App\Support\Platform\ApprovalRulesService;
use App\Support\QuotationRevisionService;
use App\Support\Communications\CommunicationEventDispatcher;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class QuotationApprovalService
{
    public function __construct(
        protected ApprovalRulesService $rules,
        protected ApprovalEnforcementEngine $engine,
        protected ApprovalChainEngine $chainEngine,
        protected ApprovalDelegationService $delegations,
        protected NotificationService $notifications,
    ) {}

    public function requiresApproval(Quotation $quotation): bool
    {
        return $this->resolveRuleType($quotation) !== null;
    }

    public function resolveRuleType(Quotation $quotation): ?ApprovalRuleType
    {
        $discountPercent = (float) $quotation->subtotal > 0
            ? round(((float) $quotation->discount_amount / (float) $quotation->subtotal) * 100, 2)
            : 0.0;

        if ($this->rules->requiresApproval(
            ApprovalRuleType::DiscountApproval,
            null,
            $discountPercent,
            $quotation->company_id,
            $quotation->branch_id,
        )) {
            return ApprovalRuleType::DiscountApproval;
        }

        if ($this->rules->requiresApproval(
            ApprovalRuleType::QuotationApproval,
            (float) $quotation->total_amount,
            null,
            $quotation->company_id,
            $quotation->branch_id,
        )) {
            return ApprovalRuleType::QuotationApproval;
        }

        return null;
    }

    /**
     * @return array{amount: float, percent: float}
     */
    public function approvalContext(Quotation $quotation): array
    {
        $discountPercent = (float) $quotation->subtotal > 0
            ? round(((float) $quotation->discount_amount / (float) $quotation->subtotal) * 100, 2)
            : 0.0;

        return [
            'amount' => (float) $quotation->total_amount,
            'percent' => $discountPercent,
        ];
    }

    public function submit(Quotation $quotation, int $userId): Quotation
    {
        if ($quotation->status !== QuotationStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft quotations can be submitted for approval.'),
            ]);
        }

        $ruleType = $this->resolveRuleType($quotation);
        $context = $this->approvalContext($quotation);

        if ($ruleType === null) {
            $quotation->transitionTo(QuotationStatus::Sent);
            $quotation->update(['approved_at' => now(), 'approved_by' => $userId]);
            $this->dispatchQuotationSent($quotation->fresh(), User::query()->find($userId));
        } else {
            $quotation->transitionTo(QuotationStatus::PendingApproval);
            $this->engine->beginApproval($quotation, $ruleType, $context);
            $this->notifySubmitted($quotation->fresh());
        }

        QuotationRevisionService::snapshot($quotation->fresh(), $userId);
        ActivityLogger::log('quote_submitted_for_approval', $quotation, $userId, [
            'requires_approval' => $ruleType !== null,
            'rule_type' => $ruleType?->value,
        ]);

        return $quotation->fresh();
    }

    public function approve(Quotation $quotation, User $actor, ?string $reason = null): Quotation
    {
        if ($quotation->status !== QuotationStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only pending quotations can be approved.'),
            ]);
        }

        $this->recordDelegationIfNeeded($quotation, $actor);

        $run = $this->engine->latestRun($quotation);

        if ($run !== null) {
            $this->engine->recordApproval($quotation, $actor, $reason);
        } elseif (! $actor->can('quotations.approve')) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to approve this quotation.'),
            ]);
        }

        if ($this->engine->hasApprovedChain($quotation->fresh())) {
            $quotation->transitionTo(QuotationStatus::Approved);
            $quotation->update([
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);

            ActivityLogger::log('quote_approved', $quotation, $actor->id, [
                'reason' => $reason,
            ]);
            app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, $actor);
            $this->notifyApproved($quotation->fresh(), $actor);
        } else {
            ActivityLogger::log('quote_approval_step_recorded', $quotation, $actor->id, [
                'reason' => $reason,
            ]);
        }

        QuotationRevisionService::snapshot($quotation->fresh(), $actor->id);

        return $quotation->fresh(['preparer', 'approver']);
    }

    public function reject(Quotation $quotation, User $actor, string $reason): Quotation
    {
        if ($quotation->status !== QuotationStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only pending quotations can be rejected.'),
            ]);
        }

        $run = $this->engine->latestRun($quotation);

        if ($run !== null) {
            $this->engine->recordRejection($quotation, $actor, $reason);
        } elseif (! $this->actorCanReject($quotation, $actor)) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to reject this quotation.'),
            ]);
        }

        $quotation->transitionTo(QuotationStatus::Draft);

        ActivityLogger::log('quote_approval_rejected', $quotation, $actor->id, [
            'reason' => $reason,
        ]);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Rejected, $quotation, $actor);
        $this->notifyRejected($quotation->fresh(), $actor, $reason);
        QuotationRevisionService::snapshot($quotation->fresh(), $actor->id);

        return $quotation->fresh(['preparer', 'approver']);
    }

    public function send(Quotation $quotation): Quotation
    {
        if ($quotation->status !== QuotationStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('Quotation must be approved before it can be sent.'),
            ]);
        }

        $this->assertCanSend($quotation);

        $quotation->transitionTo(QuotationStatus::Sent);
        QuotationRevisionService::snapshot($quotation, $quotation->approved_by ?? $quotation->prepared_by);
        $this->dispatchQuotationSent($quotation->fresh());

        return $quotation->fresh();
    }

    public function dispatchQuotationSent(Quotation $quotation, ?User $actor = null): void
    {
        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::QuotationSent,
            $quotation,
            $actor,
        );
    }

    public function assertCanSend(Quotation $quotation): void
    {
        if (! $this->engine->hasApprovedChain($quotation)) {
            throw ValidationException::withMessages([
                'approval' => __('Approval chain must be completed before sending.'),
            ]);
        }
    }

    /**
     * @return array{
     *     requires_approval: bool,
     *     rule_type: string|null,
     *     run_status: string|null,
     *     chain_complete: bool,
     *     current_step: array<string, mixed>|null,
     *     history: list<array<string, mixed>>,
     * }
     */
    public function presentApprovalState(Quotation $quotation): array
    {
        $run = $this->engine->latestRun($quotation);
        $ruleType = $run?->approval_rule_type ?? $this->resolveRuleType($quotation);

        $currentStep = null;
        if ($run && $run->status === ApprovalChainRunStatus::Pending) {
            $actionable = $this->chainEngine->actionableStepRecords($run)->first();
            if ($actionable) {
                $currentStep = $this->mapStepRecord($actionable);
            }
        }

        $history = $run
            ? $run->stepRecords()
                ->with(['step', 'actor'])
                ->orderBy('step_number')
                ->get()
                ->map(fn (ApprovalChainStepRecord $record) => $this->mapStepRecord($record))
                ->all()
            : [];

        return [
            'requires_approval' => $this->requiresApproval($quotation)
                || $quotation->status === QuotationStatus::PendingApproval
                || $quotation->status === QuotationStatus::Approved,
            'rule_type' => $ruleType?->value,
            'run_status' => $run?->status->value,
            'chain_complete' => $this->engine->hasApprovedChain($quotation),
            'current_step' => $currentStep,
            'history' => $history,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapStepRecord(ApprovalChainStepRecord $record): array
    {
        return [
            'step_number' => $record->step_number,
            'approver' => $record->step?->approverLabel() ?? __('Unassigned'),
            'status' => $record->status->value,
            'status_label' => str($record->status->value)->headline(),
            'acted_by' => $record->actor?->name,
            'acted_at' => $record->acted_at,
            'notes' => $record->notes,
        ];
    }

    protected function actorCanReject(Quotation $quotation, User $actor): bool
    {
        if ($actor->can('quotations.approve')) {
            return true;
        }

        $run = $this->engine->latestRun($quotation);
        $ruleType = $run?->approval_rule_type ?? ApprovalRuleType::QuotationApproval;

        return $this->delegations->canActAsDelegate(
            $actor,
            $ruleType->value,
            $this->approvalModule(),
            $quotation->company_id,
            $quotation->branch_id,
            'quotations.approve',
        );
    }

    protected function recordDelegationIfNeeded(Quotation $quotation, User $actor): void
    {
        if ($actor->can('quotations.approve')) {
            return;
        }

        $run = $this->engine->latestRun($quotation);
        $ruleType = $run?->approval_rule_type ?? ApprovalRuleType::QuotationApproval;

        $delegation = $this->delegations->resolveActiveDelegation(
            $actor,
            $ruleType->value,
            $this->approvalModule(),
            $quotation->company_id,
            $quotation->branch_id,
            'quotations.approve',
        );

        if ($delegation) {
            $this->delegations->recordDelegatedApproval(
                $actor,
                $quotation,
                $delegation,
                'quotation.approved_via_delegation',
                $this->approvalModule(),
            );
        }
    }

    protected function notifySubmitted(Quotation $quotation): void
    {
        $run = $this->engine->latestRun($quotation);
        if ($run === null) {
            return;
        }

        $recipients = $this->approverUserIdsForRun($run);

        foreach ($recipients as $userId) {
            $this->notifications->create([
                'company_id' => $quotation->company_id,
                'recipient_user_id' => $userId,
                'type' => NotificationType::QuotationSubmitted,
                'title' => __('Quotation submitted for approval'),
                'body' => __(':number requires your approval.', ['number' => $quotation->quotation_number]),
                'action_url' => Route::has('admin.quotations.show')
                    ? route('admin.quotations.show', $quotation)
                    : null,
                'required_permission' => 'quotations.approve',
                'subject_type' => Quotation::class,
                'subject_id' => $quotation->id,
                'created_by' => $quotation->prepared_by,
            ]);
        }
    }

    protected function notifyApproved(Quotation $quotation, User $actor): void
    {
        if (! $quotation->prepared_by || $quotation->prepared_by === $actor->id) {
            return;
        }

        $this->notifications->create([
            'company_id' => $quotation->company_id,
            'recipient_user_id' => $quotation->prepared_by,
            'type' => NotificationType::QuotationApproved,
            'title' => __('Quotation approved'),
            'body' => __(':number has been approved and is ready to send.', ['number' => $quotation->quotation_number]),
            'action_url' => Route::has('admin.quotations.show')
                ? route('admin.quotations.show', $quotation)
                : null,
            'required_permission' => 'quotations.view',
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
            'created_by' => $actor->id,
        ]);
    }

    protected function notifyRejected(Quotation $quotation, User $actor, string $reason): void
    {
        if (! $quotation->prepared_by || $quotation->prepared_by === $actor->id) {
            return;
        }

        $this->notifications->create([
            'company_id' => $quotation->company_id,
            'recipient_user_id' => $quotation->prepared_by,
            'type' => NotificationType::QuotationRejected,
            'title' => __('Quotation approval rejected'),
            'body' => __(':number was returned to draft. Reason: :reason', [
                'number' => $quotation->quotation_number,
                'reason' => $reason,
            ]),
            'action_url' => Route::has('admin.quotations.show')
                ? route('admin.quotations.show', $quotation)
                : null,
            'required_permission' => 'quotations.view',
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
            'created_by' => $actor->id,
        ]);
    }

    protected function approvalModule(): string
    {
        return (string) config('chain_registry.document_types.quotation.module', 'sales');
    }

    /**
     * @return list<int>
     */
    protected function approverUserIdsForRun(ApprovalChainRun $run): array
    {
        $run->loadMissing(['stepRecords.step.approverUser']);

        return $run->stepRecords
            ->filter(fn (ApprovalChainStepRecord $record) => in_array($record->status, [
                ApprovalChainStepStatus::Pending,
                ApprovalChainStepStatus::Escalated,
            ], true))
            ->map(fn (ApprovalChainStepRecord $record) => $record->step?->approver_user_id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
