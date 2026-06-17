<?php

namespace App\Support\Communications;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateCategory;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\DomainCommunicationEvent;
use App\Models\Communications\CommunicationTemplate;
use App\Models\User;
use App\Support\Governance\WorkflowCommunicationService;
use Illuminate\Database\Eloquent\Model;

class DomainCommunicationChannelDispatcher
{
    public function __construct(
        protected WorkflowCommunicationService $workflowComms,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function dispatch(
        DomainCommunicationEvent $event,
        Model $subject,
        array $context,
        ?User $actor = null,
    ): array {
        if (! $this->isJourneyEvent($event)) {
            return ['skipped' => true, 'reason' => 'not_journey_event'];
        }

        $category = $event->templateCategory();

        if ($category === null) {
            return ['skipped' => true, 'reason' => 'no_template_category'];
        }

        $variables = $this->templateVariables($context);
        $results = [];

        foreach ($this->enabledChannels() as $channel) {
            $results[$channel] = $this->dispatchChannel(
                $channel,
                $event,
                $subject,
                $context,
                $category,
                $variables,
                $actor,
            );
        }

        return [
            'dispatched' => true,
            'channels' => $results,
        ];
    }

    protected function isJourneyEvent(DomainCommunicationEvent $event): bool
    {
        return in_array($event->value, config('customer_journey_communications.journey_events', []), true);
    }

    /**
     * @return list<string>
     */
    protected function enabledChannels(): array
    {
        return config('customer_journey_communications.channels', ['email', 'sms', 'whatsapp']);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string|null>  $variables
     * @return array<string, mixed>
     */
    protected function dispatchChannel(
        string $channel,
        DomainCommunicationEvent $event,
        Model $subject,
        array $context,
        CommunicationTemplateCategory $category,
        array $variables,
        ?User $actor,
    ): array {
        if ($channel === 'email' && $event === DomainCommunicationEvent::QuotationSent) {
            return [
                'skipped' => true,
                'reason' => 'document_email_handled',
            ];
        }

        $template = $this->resolveTemplate((int) $context['company_id'], $category, $channel);

        $config = match ($channel) {
            'email' => $this->emailConfig($context, $template, $variables, $event),
            'sms' => $this->smsConfig($context, $template, $variables, $event),
            'whatsapp' => $this->whatsappConfig($context, $template, $variables, $event),
            default => null,
        };

        if ($config === null) {
            return ['skipped' => true, 'reason' => 'unsupported_channel'];
        }

        if ($config['skipped'] ?? false) {
            return $config;
        }

        try {
            return match ($channel) {
                'email' => $this->workflowComms->sendEmail($subject, $config, $actor),
                'sms' => $this->workflowComms->sendSms($subject, $config, $actor),
                'whatsapp' => $this->workflowComms->sendWhatsapp($subject, $config, $actor),
                default => ['skipped' => true, 'reason' => 'unsupported_channel'],
            };
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'channel' => $channel,
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function resolveTemplate(
        int $companyId,
        CommunicationTemplateCategory $category,
        string $channel,
    ): ?CommunicationTemplate {
        $communicationChannel = match ($channel) {
            'email' => CommunicationChannel::Email,
            'sms' => CommunicationChannel::Sms,
            'whatsapp' => CommunicationChannel::WhatsApp,
            default => null,
        };

        if ($communicationChannel === null) {
            return null;
        }

        return CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('category', $category)
            ->where('channel', $communicationChannel)
            ->where('status', CommunicationTemplateStatus::Active)
            ->orderByDesc('version_number')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string|null>  $variables
     * @return array<string, mixed>|null
     */
    protected function emailConfig(
        array $context,
        ?CommunicationTemplate $template,
        array $variables,
        DomainCommunicationEvent $event,
    ): ?array {
        if (! filled($context['customer_email'] ?? null)) {
            return ['skipped' => true, 'reason' => 'no_recipient_email'];
        }

        if ($template !== null) {
            return [
                'recipient_email' => $context['customer_email'],
                'template_code' => $template->code,
            ];
        }

        $rendered = $this->fallbackBody($event, $variables);

        return [
            'recipient_email' => $context['customer_email'],
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string|null>  $variables
     * @return array<string, mixed>|null
     */
    protected function smsConfig(
        array $context,
        ?CommunicationTemplate $template,
        array $variables,
        DomainCommunicationEvent $event,
    ): ?array {
        if (! filled($context['customer_phone'] ?? null)) {
            return ['skipped' => true, 'reason' => 'no_recipient_phone'];
        }

        if ($template !== null) {
            return [
                'recipient_phone' => $context['customer_phone'],
                'template_code' => $template->code,
                'queue' => true,
            ];
        }

        return [
            'recipient_phone' => $context['customer_phone'],
            'message' => $this->fallbackBody($event, $variables)['body'],
            'queue' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string|null>  $variables
     * @return array<string, mixed>|null
     */
    protected function whatsappConfig(
        array $context,
        ?CommunicationTemplate $template,
        array $variables,
        DomainCommunicationEvent $event,
    ): ?array {
        if (! filled($context['customer_phone'] ?? null)) {
            return ['skipped' => true, 'reason' => 'no_recipient_phone'];
        }

        if ($template !== null) {
            return [
                'recipient_phone' => $context['customer_phone'],
                'template_code' => $template->code,
            ];
        }

        return [
            'recipient_phone' => $context['customer_phone'],
            'message' => $this->fallbackBody($event, $variables)['body'],
        ];
    }

    /**
     * @param  array<string, string|null>  $variables
     * @return array{subject: string, body: string}
     */
    protected function fallbackBody(DomainCommunicationEvent $event, array $variables): array
    {
        $label = $variables['subject_label'] ?? $event->label();
        $name = $variables['customer_name'] ?? __('Customer');

        return [
            'subject' => $event->label(),
            'body' => __('Hello :name, :event regarding :document.', [
                'name' => $name,
                'event' => $event->label(),
                'document' => $label,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string|null>
     */
    protected function templateVariables(array $context): array
    {
        return [
            'customer_name' => $context['customer_name'] ?? null,
            'customer_email' => $context['customer_email'] ?? null,
            'customer_phone' => $context['customer_phone'] ?? null,
            'document_number' => $context['subject_label'] ?? null,
            'subject_label' => $context['subject_label'] ?? null,
        ];
    }
}
