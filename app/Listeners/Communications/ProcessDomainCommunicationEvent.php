<?php

namespace App\Listeners\Communications;

use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Enums\DomainCommunicationEvent;
use App\Support\Communications\CommunicationEventContext;
use App\Support\Communications\CommunicationLogService;
use App\Support\Communications\DomainCommunicationChannelDispatcher;
use App\Support\Communications\FollowUpDueAutomationService;
use App\Support\Integrations\WebhookService;

class ProcessDomainCommunicationEvent
{
    public function __construct(
        protected CommunicationEventContext $context,
        protected CommunicationLogService $logs,
        protected WebhookService $webhooks,
        protected DomainCommunicationChannelDispatcher $channelDispatcher,
        protected FollowUpDueAutomationService $followUpAutomation,
    ) {}

    public function handle(DomainCommunicationEventRaised $raised): void
    {
        $context = $this->context->resolve($raised->event, $raised->subject, $raised->metadata);

        if ($context['company_id'] <= 0) {
            return;
        }

        $this->logs->record([
            'company_id' => $context['company_id'],
            'branch_id' => $context['branch_id'],
            'channel' => CommunicationLogChannel::System,
            'communication_type' => $raised->event->logType(),
            'subject' => $raised->event->label(),
            'message_body' => __('Domain communication event :event for :subject.', [
                'event' => $raised->event->label(),
                'subject' => $context['subject_label'],
            ]),
            'status' => CommunicationLogStatus::Queued,
            'priority' => $raised->event->notificationType()?->defaultPriority(),
            'template_code' => $raised->event->value,
            'source_type' => $context['source_type'],
            'source_id' => $context['source_id'],
            'created_by' => $raised->actor?->id,
            'provider_response' => $context['metadata'],
            'recipients' => $this->recipients($context),
        ]);

        $webhookEvent = $raised->event->webhookEvent();

        if ($webhookEvent !== null) {
            $this->webhooks->dispatchForCompany($context['company_id'], $webhookEvent, [
                'event' => $webhookEvent->value,
                'domain_event' => $raised->event->value,
                'timestamp' => now()->toIso8601String(),
                'company_id' => $context['company_id'],
                'branch_id' => $context['branch_id'],
                'source_type' => $context['source_type'],
                'source_id' => $context['source_id'],
                'customer_id' => $context['customer_id'],
                'subject_label' => $context['subject_label'],
                'metadata' => $context['metadata'],
            ]);
        }

        if ($raised->event === DomainCommunicationEvent::FollowUpDue) {
            $this->followUpAutomation->process($raised->subject);

            return;
        }

        $this->channelDispatcher->dispatch(
            $raised->event,
            $raised->subject,
            $context,
            $raised->actor,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{recipient_type: string, recipient_id?: int|null, display_name?: string, phone?: string, email?: string}>
     */
    protected function recipients(array $context): array
    {
        if ($context['customer_id'] === null) {
            return [];
        }

        return [[
            'recipient_type' => 'customer',
            'recipient_id' => $context['customer_id'],
            'display_name' => $context['customer_name'],
            'phone' => $context['customer_phone'],
            'email' => $context['customer_email'],
        ]];
    }
}
