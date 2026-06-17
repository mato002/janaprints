<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Models\Communications\EmailAttachment;
use App\Models\Communications\EmailDeliveryEvent;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\EmailRecipient;
use App\Models\Hr\PayrollPayslip;
use App\Support\Communications\CommunicationLogService;
use App\Support\Hr\PayrollConfidentialityService;
use App\Support\Communications\TemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmailMessageService
{
    public function __construct(
        protected EmailProviderGateway $gateway,
        protected EmailAccountService $accounts,
        protected CommunicationLogService $communicationLogs,
        protected TemplateRenderer $renderer,
        protected CommunicationEntityLinkResolver $entityLinks,
        protected EmailAttachmentMaterializer $attachmentMaterializer,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = EmailMessage::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['account', 'creator', 'campaign', 'attachments']);

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($sender = $filters['sender'] ?? null) {
            $query->where('email_account_id', $sender);
        }

        if ($module = $filters['module'] ?? null) {
            $query->where('provider_response->metadata->module', $module);
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (($filters['view'] ?? null) === 'queued') {
            $query->whereIn('status', [
                EmailDeliveryStatus::Queued,
                EmailDeliveryStatus::Sending,
            ]);
        }

        if (($filters['view'] ?? null) === 'sent') {
            $query->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
            ]);
        }

        if (($filters['view'] ?? null) === 'inbox') {
            $query->whereIn('status', [
                EmailDeliveryStatus::Failed,
                EmailDeliveryStatus::Bounced,
            ]);
        }

        if (($filters['view'] ?? null) === 'drafts') {
            $query->where('status', EmailDeliveryStatus::Draft);
        }

        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function (Builder $inner) use ($q) {
                $inner->where('subject', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%");
            });
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * @param  array{to: list<string>, cc?: list<string>, bcc?: list<string>, subject: string, body: string, status?: EmailDeliveryStatus, communication_template_id?: int, email_template_id?: int, sender_purpose?: string, attachments?: list<array{attachment_type: string, attachable_type?: string, attachable_id?: int, label?: string, file_path?: string}>}  $payload
     */
    public function compose(int $companyId, int $userId, array $payload, bool $sendNow = true): EmailMessage
    {
        $purpose = (string) ($payload['sender_purpose'] ?? 'system_alert');
        $account = $this->accounts->accountForPurpose($companyId, $userId, $purpose);
        $deliverSynchronously = $sendNow && $this->shouldDeliverSynchronously($purpose);

        $message = DB::transaction(function () use ($companyId, $userId, $payload, $sendNow, $account, $deliverSynchronously) {
            $status = $sendNow ? EmailDeliveryStatus::Queued : EmailDeliveryStatus::Draft;
            $metadata = $payload['metadata'] ?? [];
            $senderPurpose = (string) ($payload['sender_purpose'] ?? 'system_alert');
            $metadata = app(PayrollConfidentialityService::class)->markConfidentialMetadata($metadata, $senderPurpose);

            $message = EmailMessage::query()->create([
                'company_id' => $companyId,
                'branch_id' => $payload['branch_id'] ?? tenant()->branchId(),
                'email_account_id' => $account->id,
                'to_emails' => $payload['to'],
                'cc_emails' => $payload['cc'] ?? [],
                'bcc_emails' => $payload['bcc'] ?? [],
                'subject' => $payload['subject'],
                'body' => $payload['body'],
                'communication_template_id' => $payload['communication_template_id'] ?? null,
                'email_template_id' => $payload['email_template_id'] ?? null,
                'status' => $status,
                'provider_response' => [
                    'metadata' => $metadata,
                    'sender_purpose' => $senderPurpose,
                ],
                'created_by' => $userId,
                'queued_at' => $sendNow ? now() : null,
            ]);

            foreach ($payload['attachments'] ?? [] as $attachment) {
                EmailAttachment::query()->create([
                    'email_message_id' => $message->id,
                    'attachment_type' => $attachment['attachment_type'],
                    'attachable_type' => $attachment['attachable_type'] ?? null,
                    'attachable_id' => $attachment['attachable_id'] ?? null,
                    'label' => $attachment['label'] ?? null,
                    'file_path' => $attachment['file_path'] ?? null,
                ]);
            }

            $this->recordDeliveryEvent($message, 'created', $status->value, null, $userId);

            if ($sendNow && ! $deliverSynchronously) {
                return $this->dispatch($message->fresh(['attachments', 'recipient']));
            }

            return $message->fresh(['attachments']);
        });

        $this->communicationLogs->recordFromEmailMessage(
            $message->fresh(['attachments', 'recipient', 'communicationTemplate']),
            'created',
            $userId,
        );

        if ($deliverSynchronously) {
            return $this->deliver($message->fresh(['attachments', 'recipient', 'communicationTemplate']));
        }

        return $message;
    }

    protected function shouldDeliverSynchronously(string $purpose): bool
    {
        return in_array($purpose, config('communications.sync_deliver_purposes', []), true);
    }

    public function sendDraft(EmailMessage $message, ?int $actorId = null): EmailMessage
    {
        $message->update(['status' => EmailDeliveryStatus::Queued, 'queued_at' => now()]);

        $message = $message->fresh(['attachments', 'recipient', 'communicationTemplate']);

        $this->recordDeliveryEvent($message, 'send_draft', EmailDeliveryStatus::Queued->value, null, $actorId);
        $this->communicationLogs->recordFromEmailMessage($message, 'send_draft', $actorId);

        return $this->dispatch($message);
    }

    public function dispatch(EmailMessage $message): EmailMessage
    {
        if ($message->status === EmailDeliveryStatus::Draft) {
            $message->update([
                'status' => EmailDeliveryStatus::Queued,
                'queued_at' => now(),
            ]);
            $message = $message->fresh(['attachments', 'recipient']);
        }

        if ($message->status !== EmailDeliveryStatus::Queued) {
            return $message;
        }

        SendEmailMessageJob::dispatch($message->id);

        return $message->fresh(['attachments', 'recipient']);
    }

    public function deliver(EmailMessage $message): EmailMessage
    {
        $message = $message->fresh(['account', 'attachments', 'recipient', 'communicationTemplate']);

        if (! $this->canDeliver($message)) {
            return $message;
        }

        $claimed = EmailMessage::query()
            ->whereKey($message->id)
            ->whereIn('status', [EmailDeliveryStatus::Queued, EmailDeliveryStatus::Sending])
            ->update(['status' => EmailDeliveryStatus::Sending]);

        if ($claimed === 0) {
            return $message->fresh(['account', 'attachments', 'recipient', 'communicationTemplate']);
        }

        $message->refresh()->load(['account', 'attachments', 'recipient', 'communicationTemplate']);

        $this->attachmentMaterializer->materialize($message);
        $message->refresh()->load(['account', 'attachments', 'recipient', 'communicationTemplate']);

        $account = $message->account ?? $this->accounts->accountForPurpose(
            (int) $message->company_id,
            (int) ($message->created_by ?? 1),
        );

        $result = $this->gateway->send($account, $message);

        $existingResponse = $message->provider_response ?? [];
        $metadata = $existingResponse['metadata'] ?? [];
        $retryCount = (int) ($existingResponse['retry_count'] ?? 0);

        $providerResponse = array_merge(
            is_array($result->payload) ? $result->payload : [],
            [
                'metadata' => $metadata,
                'retry_count' => $retryCount,
                'last_attempt_at' => now()->toIso8601String(),
            ],
        );

        $message->update([
            'status' => $result->status,
            'provider_message_ref' => $result->providerMessageRef,
            'provider_response' => $providerResponse,
            'sent_at' => in_array($result->status, [EmailDeliveryStatus::Sent, EmailDeliveryStatus::Delivered], true) ? now() : null,
            'delivered_at' => $result->status === EmailDeliveryStatus::Delivered ? now() : null,
            'failed_at' => in_array($result->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true) ? now() : null,
            'failure_reason' => $result->error,
        ]);

        $this->recordDeliveryEvent($message, 'provider_response', $result->status->value, $result->payload);

        $this->communicationLogs->recordFromEmailMessage($message->fresh(['attachments', 'recipient', 'communicationTemplate']));

        if (in_array($result->status, [EmailDeliveryStatus::Sent, EmailDeliveryStatus::Delivered], true)) {
            $this->markPayslipEmailed($message->fresh());
        }

        return $message->fresh(['account', 'attachments', 'recipient', 'communicationTemplate']);
    }

    protected function markPayslipEmailed(EmailMessage $message): void
    {
        $metadata = ($message->provider_response ?? [])['metadata'] ?? [];

        if (($metadata['entity_type'] ?? null) !== 'payroll_payslip') {
            return;
        }

        $payslipId = (int) ($metadata['entity_id'] ?? 0);

        if ($payslipId <= 0) {
            return;
        }

        PayrollPayslip::query()
            ->whereKey($payslipId)
            ->update(['emailed_at' => now()]);
    }

    public function cancel(EmailMessage $message, ?int $actorId = null): EmailMessage
    {
        if ($message->status !== EmailDeliveryStatus::Queued) {
            throw ValidationException::withMessages([
                'status' => __('Only queued emails can be cancelled.'),
            ]);
        }

        $message->update([
            'status' => EmailDeliveryStatus::Cancelled,
        ]);

        $this->recordDeliveryEvent(
            $message,
            'cancelled',
            EmailDeliveryStatus::Cancelled->value,
            null,
            $actorId,
        );

        $this->communicationLogs->recordFromEmailMessage(
            $message->fresh(['attachments', 'recipient', 'communicationTemplate']),
            'cancelled',
            $actorId,
        );

        return $message->fresh(['account', 'attachments', 'recipient', 'communicationTemplate', 'deliveryEvents']);
    }

    public function retry(EmailMessage $message, ?int $actorId = null): EmailMessage
    {
        if (! in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true)) {
            throw ValidationException::withMessages([
                'status' => __('Only failed emails can be retried.'),
            ]);
        }

        $response = $message->provider_response ?? [];
        $retryCount = (int) ($response['retry_count'] ?? 0) + 1;

        $message->update([
            'status' => EmailDeliveryStatus::Queued,
            'queued_at' => now(),
            'failed_at' => null,
            'failure_reason' => null,
            'provider_response' => array_merge($response, [
                'retry_count' => $retryCount,
                'last_attempt_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->recordDeliveryEvent(
            $message,
            'retry_requested',
            EmailDeliveryStatus::Queued->value,
            ['retry_count' => $retryCount],
            $actorId,
        );

        $message = $message->fresh(['attachments', 'recipient', 'communicationTemplate']);
        $this->communicationLogs->recordFromEmailMessage($message, 'retry_requested', $actorId);

        return $this->dispatch($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentDetail(EmailMessage $message): array
    {
        $message->loadMissing(['account', 'attachments', 'deliveryEvents.creator', 'creator']);

        $metadata = $message->provider_response['metadata'] ?? [];
        $retryCount = (int) ($message->provider_response['retry_count'] ?? 0);
        $lastAttempt = $message->provider_response['last_attempt_at'] ?? null;

        return [
            'id' => $message->id,
            'subject' => $message->subject,
            'sender' => [
                'email' => $message->account?->from_email,
                'name' => $message->account?->from_name,
                'provider' => $message->account?->provider?->label(),
            ],
            'recipients' => [
                'to' => $message->to_emails,
                'cc' => $message->cc_emails,
                'bcc' => $message->bcc_emails,
            ],
            'status' => $message->status->value,
            'status_label' => $message->status->label(),
            'created_at' => $message->created_at?->toIso8601String(),
            'created_at_formatted' => $message->created_at?->format('d M Y H:i'),
            'sent_at' => $message->sent_at?->toIso8601String(),
            'sent_at_formatted' => $message->sent_at?->format('d M Y H:i'),
            'failed_at' => $message->failed_at?->toIso8601String(),
            'failed_at_formatted' => $message->failed_at?->format('d M Y H:i'),
            'retry_count' => $retryCount,
            'last_attempt_at' => $lastAttempt,
            'last_attempt_at_formatted' => $lastAttempt ? \Illuminate\Support\Carbon::parse($lastAttempt)->format('d M Y H:i') : null,
            'failure_reason' => $message->failure_reason,
            'module' => $metadata['module'] ?? null,
            'entity_type' => $metadata['entity_type'] ?? null,
            'entity_id' => $metadata['entity_id'] ?? null,
            'document_number' => $metadata['document_number'] ?? null,
            'related_entity' => $this->entityLinks->resolve($metadata),
            'attachments' => $message->attachments->map(fn ($attachment) => [
                'label' => $attachment->label,
                'type' => $attachment->attachment_type,
            ])->values()->all(),
            'audit_timeline' => $message->deliveryEvents->map(fn ($event) => [
                'event' => $event->event,
                'status' => $event->status_snapshot,
                'created_at' => $event->created_at?->format('d M Y H:i'),
                'actor' => $event->creator?->name,
                'payload' => $event->payload,
            ])->values()->all(),
        ];
    }

    public function markFailed(EmailMessage $message, string $reason): EmailMessage
    {
        $message->update([
            'status' => EmailDeliveryStatus::Failed,
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);

        $this->recordDeliveryEvent($message, 'failed', EmailDeliveryStatus::Failed->value, [
            'failure_reason' => $reason,
        ]);

        $this->communicationLogs->recordFromEmailMessage($message->fresh(['attachments', 'recipient', 'communicationTemplate']));

        return $message->fresh(['account', 'attachments', 'recipient', 'communicationTemplate']);
    }

    public function createForRecipient(
        EmailRecipient $recipient,
        int $userId,
        string $subject,
        string $body,
    ): EmailMessage {
        $campaign = $recipient->campaign;
        $account = $campaign->account ?? $this->accounts->accountForPurpose($campaign->company_id, $userId, 'system_alert');

        $message = EmailMessage::query()->create([
            'company_id' => $campaign->company_id,
            'branch_id' => $campaign->branch_id,
            'email_campaign_id' => $campaign->id,
            'email_recipient_id' => $recipient->id,
            'email_account_id' => $account->id,
            'to_emails' => [['email' => $recipient->email, 'name' => $recipient->display_name]],
            'cc_emails' => $campaign->cc_emails ?? [],
            'bcc_emails' => $campaign->bcc_emails ?? [],
            'subject' => $subject,
            'body' => $body,
            'communication_template_id' => $campaign->communication_template_id,
            'email_template_id' => $campaign->email_template_id,
            'status' => EmailDeliveryStatus::Queued,
            'queued_at' => now(),
            'created_by' => $userId,
        ]);

        return $this->dispatch($message);
    }

    public function recordDeliveryEvent(
        EmailMessage $message,
        string $event,
        ?string $statusSnapshot = null,
        ?array $payload = null,
        ?int $createdBy = null,
    ): EmailDeliveryEvent {
        return EmailDeliveryEvent::query()->create([
            'email_message_id' => $message->id,
            'event' => $event,
            'status_snapshot' => $statusSnapshot,
            'payload' => $payload,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * @return Collection<int, EmailMessage>
     */
    public function forEntity(string $recipientType, int $recipientId, int $companyId, int $limit = 15): \Illuminate\Support\Collection
    {
        return EmailMessage::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereHas('recipient', function (Builder $q) use ($recipientType, $recipientId) {
                $q->where('source_type', $recipientType)->where('source_id', $recipientId);
            })
            ->with(['attachments', 'account'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    protected function canDeliver(EmailMessage $message): bool
    {
        return in_array($message->status, [
            EmailDeliveryStatus::Queued,
            EmailDeliveryStatus::Sending,
        ], true);
    }
}
