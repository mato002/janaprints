<?php

namespace App\Support\Governance;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\ApprovalRuleType;
use App\Enums\NotificationType;
use App\Enums\WorkflowRuleActionType;
use App\Models\Crm\CustomerActivity;
use App\Models\Governance\WorkflowRuleAction;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Communications\NotificationService;
use App\Support\QuotationConversionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class WorkflowRuleActionExecutor
{
    public function __construct(
        protected NotificationService $notifications,
        protected ApprovalChainsService $approvalChains,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(WorkflowRuleAction $action, Model $subject, ?User $actor = null): array
    {
        $config = $action->config_json ?? [];
        $actor ??= auth()->user();

        return match ($action->action_type) {
            WorkflowRuleActionType::CreateDocument => $this->createDocument($subject, $config, $actor),
            WorkflowRuleActionType::SendNotification => $this->sendNotification($subject, $config, $actor),
            WorkflowRuleActionType::SendEmail => $this->sendEmail($subject, $config),
            WorkflowRuleActionType::SendSms => $this->sendSms($subject, $config),
            WorkflowRuleActionType::AssignUser => $this->assignUser($subject, $config),
            WorkflowRuleActionType::ChangeStatus => $this->changeStatus($subject, $config),
            WorkflowRuleActionType::GenerateTask => $this->generateTask($subject, $config, $actor),
            WorkflowRuleActionType::GenerateApproval => $this->generateApproval($subject, $config, $actor),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function createDocument(Model $subject, array $config, ?User $actor): array
    {
        $target = (string) ($config['target_entity'] ?? '');

        if ($subject instanceof Quotation && $target === 'sales_order') {
            $salesOrder = QuotationConversionService::convert($subject, (int) ($actor?->id ?? $subject->created_by));

            return [
                'created_type' => $salesOrder::class,
                'created_id' => $salesOrder->id,
                'created_label' => $salesOrder->order_number,
            ];
        }

        throw ValidationException::withMessages([
            'action' => __('Document conversion from :source to :target is not supported.', [
                'source' => class_basename($subject),
                'target' => $target,
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function sendNotification(Model $subject, array $config, ?User $actor): array
    {
        $recipient = $this->resolveRecipient($subject, $config);

        if ($recipient === null) {
            return ['skipped' => true, 'reason' => 'no_recipient'];
        }

        $type = NotificationType::tryFrom((string) ($config['notification_type'] ?? ''))
            ?? NotificationType::QuotationApproved;

        $notification = $this->notifications->create([
            'company_id' => (int) $subject->company_id,
            'recipient_user_id' => $recipient->id,
            'type' => $type,
            'title' => (string) ($config['title'] ?? __('Workflow notification')),
            'body' => (string) ($config['body'] ?? __('An automated workflow action was triggered.')),
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'created_by' => $actor?->id,
        ]);

        return [
            'notification_id' => $notification?->id,
            'recipient_user_id' => $recipient->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function sendEmail(Model $subject, array $config): array
    {
        return [
            'queued' => true,
            'recipient_email' => (string) ($config['recipient_email'] ?? ''),
            'subject' => (string) ($config['subject'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function sendSms(Model $subject, array $config): array
    {
        return [
            'queued' => true,
            'recipient_phone' => (string) ($config['recipient_phone'] ?? ''),
            'message' => (string) ($config['message'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function assignUser(Model $subject, array $config): array
    {
        $userId = (int) ($config['user_id'] ?? 0);
        $field = (string) ($config['assignment_field'] ?? 'assigned_to_user_id');

        if ($userId <= 0 || ! $subject->isFillable($field)) {
            return ['skipped' => true, 'reason' => 'unsupported_assignment'];
        }

        $subject->update([$field => $userId]);

        return ['assigned_user_id' => $userId, 'field' => $field];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function changeStatus(Model $subject, array $config): array
    {
        $targetStatus = (string) ($config['target_status'] ?? '');

        if ($targetStatus === '' || ! method_exists($subject, 'transitionTo')) {
            return ['skipped' => true, 'reason' => 'unsupported_status_change'];
        }

        $statusEnum = $subject->status;
        $enumClass = $statusEnum instanceof \BackedEnum ? $statusEnum::class : null;

        if ($enumClass === null) {
            return ['skipped' => true, 'reason' => 'unsupported_status_change'];
        }

        $case = $enumClass::tryFrom($targetStatus);

        if ($case === null) {
            return ['skipped' => true, 'reason' => 'invalid_target_status'];
        }

        $subject->transitionTo($case);

        return ['target_status' => $targetStatus];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function generateTask(Model $subject, array $config, ?User $actor): array
    {
        if (! isset($subject->customer_id)) {
            return ['skipped' => true, 'reason' => 'no_customer_context'];
        }

        $activityType = ActivityType::tryFrom((string) ($config['task_type'] ?? ''))
            ?? ActivityType::FollowUp;

        $activity = CustomerActivity::query()->create([
            'company_id' => $subject->company_id,
            'branch_id' => $subject->branch_id ?? null,
            'customer_id' => $subject->customer_id,
            'user_id' => filled($config['assigned_user_id'] ?? null) ? (int) $config['assigned_user_id'] : $actor?->id,
            'activity_type' => $activityType,
            'subject' => (string) ($config['title'] ?? __('Workflow task')),
            'description' => (string) ($config['description'] ?? ''),
            'status' => ActivityStatus::Scheduled,
            'activity_at' => now()->addDay(),
        ]);

        return [
            'task_id' => $activity->id,
            'task_type' => $activity->activity_type,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function generateApproval(Model $subject, array $config, ?User $actor): array
    {
        $ruleType = ApprovalRuleType::tryFrom((string) ($config['approval_rule_type'] ?? ''));

        if ($ruleType === null) {
            return ['skipped' => true, 'reason' => 'invalid_approval_rule_type'];
        }

        $evaluation = app(\App\Support\Platform\ApprovalRulesService::class)->evaluate(
            $ruleType,
            null,
            null,
            (int) $subject->company_id,
            $subject->branch_id ?? null,
        );

        $chain = $evaluation['approval_chain'] ?? null;

        if ($chain === null) {
            return ['skipped' => true, 'reason' => 'no_approval_chain'];
        }

        $run = $this->approvalChains->startRun(
            $chain,
            $subject,
            ['amount' => $subject->total_amount ?? null],
            (int) $subject->company_id,
            $subject->branch_id ?? null,
        );

        return [
            'approval_chain_run_id' => $run->id,
            'approval_chain_id' => $chain->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function resolveRecipient(Model $subject, array $config): ?User
    {
        if (filled($config['recipient_user_id'] ?? null)) {
            return User::query()
                ->where('company_id', $subject->company_id)
                ->where('id', (int) $config['recipient_user_id'])
                ->where('is_active', true)
                ->first();
        }

        if (filled($config['recipient_role'] ?? null)) {
            return User::query()
                ->where('company_id', $subject->company_id)
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('name', (string) $config['recipient_role']))
                ->orderBy('id')
                ->first();
        }

        return null;
    }
}
