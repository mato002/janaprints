<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailAttachment;
use App\Models\Communications\EmailDeliveryEvent;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\EmailRecipient;
use App\Support\Communications\CommunicationLogService;
use App\Support\Communications\TemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmailMessageService
{
    public function __construct(
        protected EmailProviderGateway $gateway,
        protected EmailAccountService $accounts,
        protected CommunicationLogService $communicationLogs,
        protected TemplateRenderer $renderer,
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

        if (($filters['view'] ?? null) === 'sent') {
            $query->whereIn('status', [
                EmailDeliveryStatus::Sent,
                EmailDeliveryStatus::Delivered,
                EmailDeliveryStatus::Opened,
                EmailDeliveryStatus::Clicked,
                EmailDeliveryStatus::Queued,
                EmailDeliveryStatus::Sending,
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
     * @param  array{to: list<string>, cc?: list<string>, bcc?: list<string>, subject: string, body: string, status?: EmailDeliveryStatus, communication_template_id?: int, email_template_id?: int, attachments?: list<array{attachment_type: string, attachable_type?: string, attachable_id?: int, label?: string, file_path?: string}>}  $payload
     */
    public function compose(int $companyId, int $userId, array $payload, bool $sendNow = true): EmailMessage
    {
        $account = $this->accounts->ensureDefaultAccount($companyId, $userId);

        return DB::transaction(function () use ($companyId, $userId, $payload, $sendNow, $account) {
            $status = $sendNow ? EmailDeliveryStatus::Queued : EmailDeliveryStatus::Draft;

            $message = EmailMessage::query()->create([
                'company_id' => $companyId,
                'branch_id' => tenant()->branchId(),
                'email_account_id' => $account->id,
                'to_emails' => $payload['to'],
                'cc_emails' => $payload['cc'] ?? [],
                'bcc_emails' => $payload['bcc'] ?? [],
                'subject' => $payload['subject'],
                'body' => $payload['body'],
                'communication_template_id' => $payload['communication_template_id'] ?? null,
                'email_template_id' => $payload['email_template_id'] ?? null,
                'status' => $status,
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

            $this->recordDeliveryEvent($message, 'created', $status->value);

            if ($sendNow) {
                return $this->dispatch($message->fresh(['attachments', 'recipient']));
            }

            return $message->fresh(['attachments']);
        });
    }

    public function sendDraft(EmailMessage $message): EmailMessage
    {
        $message->update(['status' => EmailDeliveryStatus::Queued, 'queued_at' => now()]);

        return $this->dispatch($message->fresh(['attachments', 'recipient']));
    }

    public function dispatch(EmailMessage $message): EmailMessage
    {
        $account = $message->account;
        $result = $this->gateway->send($account, $message);

        $message->update([
            'status' => $result->status,
            'provider_message_ref' => $result->providerMessageRef,
            'provider_response' => $result->payload,
            'sent_at' => in_array($result->status, [EmailDeliveryStatus::Sent, EmailDeliveryStatus::Queued], true) ? now() : null,
            'failed_at' => $result->status === EmailDeliveryStatus::Failed ? now() : null,
            'failure_reason' => $result->error,
        ]);

        $this->recordDeliveryEvent($message, 'provider_response', $result->status->value, $result->payload);

        if ($result->status === EmailDeliveryStatus::Queued) {
            $message->update([
                'status' => EmailDeliveryStatus::Delivered,
                'delivered_at' => now(),
            ]);
            $this->recordDeliveryEvent($message, 'delivered', EmailDeliveryStatus::Delivered->value);
        }

        $this->communicationLogs->recordFromEmailMessage($message->fresh(['attachments', 'recipient', 'communicationTemplate']));

        return $message->fresh();
    }

    public function createForRecipient(
        EmailRecipient $recipient,
        int $userId,
        string $subject,
        string $body,
    ): EmailMessage {
        $campaign = $recipient->campaign;
        $account = $campaign->account ?? $this->accounts->ensureDefaultAccount($campaign->company_id, $userId);

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
}
