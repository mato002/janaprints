<?php

namespace App\Support\Governance;

use App\Enums\EmailDeliveryStatus;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Enums\SmsRecipientSource;
use App\Enums\WhatsappDeliveryStatus;
use App\Jobs\Communications\SendSmsMessageJob;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Models\User;
use App\Support\Communications\CommunicationEventContext;
use App\Support\Communications\CommunicationTemplateResolver;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\Sms\SmsCreditService;
use App\Support\Communications\Sms\SmsProviderGateway;
use App\Support\Communications\TemplateRenderer;
use App\Support\Communications\Whatsapp\WhatsappConversationService;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowCommunicationService
{
    public function __construct(
        protected CommunicationEventContext $context,
        protected CommunicationTemplateResolver $templates,
        protected TemplateRenderer $renderer,
        protected EmailMessageService $emails,
        protected SmsProviderGateway $smsGateway,
        protected SmsCreditService $smsCredits,
        protected WhatsappConversationService $whatsappConversations,
        protected WhatsappMessageService $whatsappMessages,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function sendEmail(Model $subject, array $config, ?User $actor = null): array
    {
        $content = $this->renderContent($subject, $config, includeSubject: true);
        $email = $this->resolveEmail($subject, $config);

        if ($email === null) {
            return ['skipped' => true, 'reason' => 'no_recipient_email'];
        }

        $message = $this->emails->compose(
            (int) $subject->company_id,
            (int) ($actor?->id ?? $subject->getAttribute('created_by') ?? 1),
            [
                'to' => [['email' => $email, 'name' => $content['recipient_name']]],
                'subject' => $content['subject'],
                'body' => $content['body'],
                'communication_template_id' => $content['template_id'],
            ],
            sendNow: true,
        );

        $success = ! in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true);

        return [
            'success' => $success,
            'queued' => false,
            'email_message_id' => $message->id,
            'recipient_email' => $email,
            'subject' => $content['subject'],
            'delivery_status' => $message->status->value,
            'communication_log_recorded' => true,
            'template_code' => $content['template_code'],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function sendSms(Model $subject, array $config, ?User $actor = null): array
    {
        $content = $this->renderContent($subject, $config, includeSubject: false);
        $phone = $this->resolvePhone($subject, $config);

        if ($phone === null) {
            return ['skipped' => true, 'reason' => 'no_recipient_phone'];
        }

        $body = $content['body'];
        $segments = max(1, (int) ceil(strlen($body) / 160));
        $actorId = (int) ($actor?->id ?? $subject->getAttribute('created_by') ?? 1);
        $balance = $this->smsCredits->balanceFor((int) $subject->company_id);
        $subjectContext = $this->context->resolveSubject($subject);

        $message = DB::transaction(function () use ($subject, $phone, $body, $segments, $actorId, $balance, $subjectContext) {
            $campaign = SmsCampaign::query()->create([
                'company_id' => $subject->company_id,
                'branch_id' => $subject->branch_id ?? null,
                'campaign_code' => $this->nextWorkflowCampaignCode((int) $subject->company_id),
                'name' => __('Workflow SMS :label', ['label' => $subjectContext['subject_label']]),
                'message_template' => $body,
                'send_mode' => SmsCampaignSendMode::Immediate,
                'status' => SmsCampaignStatus::Sending,
                'recipient_source' => SmsRecipientSource::Manual,
                'character_count' => strlen($body),
                'estimated_segments' => $segments,
                'cost_per_sms' => $balance->cost_per_sms,
                'total_recipients' => 1,
                'created_by' => $actorId,
                'sent_by' => $actorId,
                'started_at' => now(),
                'sent_at' => now(),
            ]);

            $recipient = SmsRecipient::query()->create([
                'sms_campaign_id' => $campaign->id,
                'source_type' => $subjectContext['source_type'],
                'source_id' => $subjectContext['source_id'],
                'phone_number' => $phone,
                'display_name' => $subjectContext['customer_name'],
            ]);

            return SmsMessage::query()->create([
                'sms_campaign_id' => $campaign->id,
                'sms_recipient_id' => $recipient->id,
                'company_id' => $subject->company_id,
                'branch_id' => $subject->branch_id ?? null,
                'phone_number' => $phone,
                'message_body' => $body,
                'queue_status' => SmsMessageQueueStatus::Queued,
                'delivery_status' => SmsDeliveryStatus::Queued,
                'segments_count' => $segments,
                'character_count' => strlen($body),
                'credit_cost' => (float) $balance->cost_per_sms * $segments,
                'attempts' => 0,
            ]);
        });

        if (($config['queue'] ?? true) === true) {
            SendSmsMessageJob::dispatch($message->id);

            return [
                'success' => true,
                'queued' => true,
                'sms_message_id' => $message->id,
                'recipient_phone' => $phone,
                'delivery_status' => SmsDeliveryStatus::Queued->value,
                'retries' => 3,
                'template_code' => $content['template_code'],
            ];
        }

        return $this->dispatchSmsSynchronously($message, $phone, $content['template_code']);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function sendWhatsapp(Model $subject, array $config, ?User $actor = null): array
    {
        $content = $this->renderContent($subject, $config, includeSubject: false);
        $phone = $this->resolvePhone($subject, $config);

        if ($phone === null) {
            return ['skipped' => true, 'reason' => 'no_recipient_phone'];
        }

        $actorId = (int) ($actor?->id ?? $subject->getAttribute('created_by') ?? 1);
        $subjectContext = $this->context->resolveSubject($subject);

        $conversation = $this->whatsappConversations->findOrCreateForContact(
            (int) $subject->company_id,
            $phone,
            $actorId,
            $subjectContext['customer_id'],
            displayName: $subjectContext['customer_name'],
        );

        $message = $this->whatsappMessages->sendManual(
            $conversation,
            $content['body'],
            $actorId,
        );

        $success = $message->status !== WhatsappDeliveryStatus::Failed;

        return [
            'success' => $success,
            'queued' => false,
            'whatsapp_message_id' => $message->id,
            'recipient_phone' => $phone,
            'delivery_status' => $message->status->value,
            'communication_log_recorded' => true,
            'template_code' => $content['template_code'],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{
     *     subject: string,
     *     body: string,
     *     template_id: ?int,
     *     template_code: ?string,
     *     recipient_name: ?string,
     * }
     */
    protected function renderContent(Model $subject, array $config, bool $includeSubject): array
    {
        $subjectContext = $this->context->resolveSubject($subject);
        $variables = $this->templateVariables($subjectContext);

        if (filled($config['template_code'] ?? null)) {
            $rendered = $this->templates->render((string) $config['template_code'], (int) $subject->company_id, $variables);

            if ($rendered !== null) {
                return [
                    'subject' => $rendered['subject'] ?? (string) ($config['subject'] ?? __('Workflow message')),
                    'body' => $rendered['body'],
                    'template_id' => $rendered['template']->id,
                    'template_code' => $rendered['template']->code,
                    'recipient_name' => $subjectContext['customer_name'],
                ];
            }
        }

        $body = (string) ($config['message'] ?? $config['body'] ?? '');
        $emailSubject = (string) ($config['subject'] ?? __('Workflow message'));

        if ($body !== '' && str_contains($body, '{{')) {
            $body = $this->renderer->render(null, $body, $variables)['body'];
        }

        if ($includeSubject && str_contains($emailSubject, '{{')) {
            $emailSubject = $this->renderer->render($emailSubject, '', $variables)['subject'] ?? $emailSubject;
        }

        return [
            'subject' => $emailSubject,
            'body' => $body,
            'template_id' => null,
            'template_code' => $config['template_code'] ?? null,
            'recipient_name' => $subjectContext['customer_name'],
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

    /**
     * @param  array<string, mixed>  $config
     */
    protected function resolveEmail(Model $subject, array $config): ?string
    {
        if (filled($config['recipient_email'] ?? null)) {
            return (string) $config['recipient_email'];
        }

        $context = $this->context->resolveSubject($subject);

        return filled($context['customer_email'] ?? null) ? (string) $context['customer_email'] : null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function resolvePhone(Model $subject, array $config): ?string
    {
        if (filled($config['recipient_phone'] ?? null)) {
            return preg_replace('/\s+/', '', (string) $config['recipient_phone']) ?: null;
        }

        $context = $this->context->resolveSubject($subject);

        return filled($context['customer_phone'] ?? null)
            ? (preg_replace('/\s+/', '', (string) $context['customer_phone']) ?: null)
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchSmsSynchronously(SmsMessage $message, string $phone, ?string $templateCode): array
    {
        $result = $this->smsGateway->send($message);

        $message->update([
            'queue_status' => $result['success'] ? SmsMessageQueueStatus::Sent : SmsMessageQueueStatus::Failed,
            'delivery_status' => $result['delivery_status'],
            'sent_at' => $result['success'] ? now() : null,
            'delivered_at' => $result['delivery_status'] === SmsDeliveryStatus::Delivered ? now() : null,
            'failure_reason' => $result['success'] ? null : ($result['response']['error'] ?? 'failed'),
            'attempts' => 1,
            'last_attempt_at' => now(),
        ]);

        app(\App\Support\Communications\CommunicationLogService::class)->recordFromSmsMessage($message->fresh());

        return [
            'success' => $result['success'],
            'queued' => false,
            'sms_message_id' => $message->id,
            'recipient_phone' => $phone,
            'delivery_status' => $result['delivery_status']->value,
            'communication_log_recorded' => true,
            'template_code' => $templateCode,
        ];
    }

    protected function nextWorkflowCampaignCode(int $companyId): string
    {
        $prefix = 'WF-SMS-'.now()->format('ymd').'-';

        do {
            $code = $prefix.Str::upper(Str::random(6));
        } while (SmsCampaign::query()->where('company_id', $companyId)->where('campaign_code', $code)->exists());

        return $code;
    }
}
