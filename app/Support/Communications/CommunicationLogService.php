<?php

namespace App\Support\Communications;

use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\EmailDeliveryStatus;
use App\Enums\WhatsappDeliveryStatus;
use App\Enums\WhatsappMessageType;
use App\Enums\NotificationPriority;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Models\Communications\CommunicationAttachment;
use App\Models\Communications\CommunicationDeliveryEvent;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationRecipient;
use App\Models\Communications\ErpNotification;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\WhatsappMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommunicationLogService
{
    /**
     * @param  array{
     *     company_id: int,
     *     channel: CommunicationLogChannel|string,
     *     communication_type: CommunicationLogType|string,
     *     message_body: string,
     *     subject?: string|null,
     *     status?: CommunicationLogStatus|string,
     *     priority?: NotificationPriority|string,
     *     branch_id?: int|null,
     *     communication_template_id?: int|null,
     *     template_code?: string|null,
     *     source_type?: string|null,
     *     source_id?: int|null,
     *     sms_campaign_id?: int|null,
     *     sender_user_id?: int|null,
     *     created_by?: int|null,
     *     sent_by?: int|null,
     *     approved_by?: int|null,
     *     sent_at?: \DateTimeInterface|null,
     *     delivered_at?: \DateTimeInterface|null,
     *     failed_at?: \DateTimeInterface|null,
     *     provider_response?: array|null,
     *     delivery_response?: array|null,
     *     recipients?: list<array{recipient_type: string, recipient_id?: int|null, display_name?: string, phone?: string, email?: string, delivery_status?: string}>,
     *     attachments?: list<array{attachment_type: string, attachable_type?: string, attachable_id?: int, label?: string}>,
     * }  $payload
     */
    public function record(array $payload): CommunicationLog
    {
        return DB::transaction(function () use ($payload) {
            $log = CommunicationLog::query()->create([
                'company_id' => $payload['company_id'],
                'branch_id' => $payload['branch_id'] ?? tenant()->branchId(),
                'reference_number' => $this->nextReference($payload['company_id']),
                'channel' => $payload['channel'],
                'communication_type' => $payload['communication_type'],
                'subject' => $payload['subject'] ?? null,
                'message_body' => $payload['message_body'],
                'status' => $payload['status'] ?? CommunicationLogStatus::Queued,
                'priority' => $payload['priority'] ?? NotificationPriority::Normal,
                'communication_template_id' => $payload['communication_template_id'] ?? null,
                'template_code' => $payload['template_code'] ?? null,
                'source_type' => $payload['source_type'] ?? null,
                'source_id' => $payload['source_id'] ?? null,
                'sms_campaign_id' => $payload['sms_campaign_id'] ?? null,
                'sender_user_id' => $payload['sender_user_id'] ?? null,
                'created_by' => $payload['created_by'] ?? null,
                'sent_by' => $payload['sent_by'] ?? null,
                'approved_by' => $payload['approved_by'] ?? null,
                'sent_at' => $payload['sent_at'] ?? null,
                'delivered_at' => $payload['delivered_at'] ?? null,
                'failed_at' => $payload['failed_at'] ?? null,
                'provider_response' => $payload['provider_response'] ?? null,
                'delivery_response' => $payload['delivery_response'] ?? null,
            ]);

            foreach ($payload['recipients'] ?? [] as $recipient) {
                CommunicationRecipient::query()->create([
                    'communication_log_id' => $log->id,
                    'recipient_type' => $recipient['recipient_type'],
                    'recipient_id' => $recipient['recipient_id'] ?? null,
                    'display_name' => $recipient['display_name'] ?? null,
                    'phone' => $recipient['phone'] ?? null,
                    'email' => $recipient['email'] ?? null,
                    'delivery_status' => $recipient['delivery_status'] ?? $log->status,
                ]);
            }

            foreach ($payload['attachments'] ?? [] as $attachment) {
                CommunicationAttachment::query()->create([
                    'communication_log_id' => $log->id,
                    'attachment_type' => $attachment['attachment_type'],
                    'attachable_type' => $attachment['attachable_type'] ?? null,
                    'attachable_id' => $attachment['attachable_id'] ?? null,
                    'label' => $attachment['label'] ?? null,
                ]);
            }

            $this->recordEvent($log, 'created', $log->status->value, null, $payload['created_by'] ?? null);

            return $log->load(['recipients', 'attachments', 'deliveryEvents', 'creator', 'sender']);
        });
    }

    public function recordEvent(
        CommunicationLog $log,
        string $event,
        ?string $statusSnapshot = null,
        ?array $payload = null,
        ?int $createdBy = null,
        ?int $recipientId = null,
    ): CommunicationDeliveryEvent {
        return CommunicationDeliveryEvent::query()->create([
            'communication_log_id' => $log->id,
            'communication_recipient_id' => $recipientId,
            'event' => $event,
            'status_snapshot' => $statusSnapshot,
            'payload' => $payload,
            'created_by' => $createdBy,
        ]);
    }

    public function recordFromSmsMessage(SmsMessage $message): CommunicationLog
    {
        $campaign = $message->campaign;
        $smsRecipient = $message->recipient;

        $status = match ($message->queue_status) {
            SmsMessageQueueStatus::Sent => $message->delivery_status === SmsDeliveryStatus::Delivered
                ? CommunicationLogStatus::Delivered
                : CommunicationLogStatus::Sent,
            SmsMessageQueueStatus::Failed => CommunicationLogStatus::Failed,
            SmsMessageQueueStatus::Processing => CommunicationLogStatus::Sending,
            SmsMessageQueueStatus::Cancelled => CommunicationLogStatus::Cancelled,
            default => CommunicationLogStatus::Queued,
        };

        $existing = CommunicationLog::query()
            ->where('source_type', SmsMessage::class)
            ->where('source_id', $message->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'sent_at' => $message->sent_at,
                'delivered_at' => $message->delivered_at,
                'failed_at' => $status === CommunicationLogStatus::Failed ? now() : null,
                'delivery_response' => ['failure_reason' => $message->failure_reason],
            ]);
            $this->recordEvent($existing, $status->value, $status->value, ['sms_message_id' => $message->id]);

            return $existing->fresh();
        }

        $recipients = [];
        if ($smsRecipient) {
            $recipients[] = [
                'recipient_type' => $smsRecipient->source_type,
                'recipient_id' => $smsRecipient->source_id,
                'display_name' => $smsRecipient->display_name,
                'phone' => $smsRecipient->phone_number,
                'delivery_status' => $status->value,
            ];
        }

        return $this->record([
            'company_id' => $message->company_id,
            'branch_id' => $message->branch_id,
            'channel' => CommunicationLogChannel::Sms,
            'communication_type' => CommunicationLogType::Broadcast,
            'subject' => $campaign?->name,
            'message_body' => $message->message_body,
            'status' => $status,
            'communication_template_id' => $campaign?->communication_template_id,
            'template_code' => $campaign?->template?->code,
            'source_type' => SmsMessage::class,
            'source_id' => $message->id,
            'sms_campaign_id' => $message->sms_campaign_id,
            'sent_by' => $campaign?->sent_by,
            'approved_by' => $campaign?->approved_by,
            'created_by' => $campaign?->created_by,
            'sent_at' => $message->sent_at,
            'delivered_at' => $message->delivered_at,
            'failed_at' => $status === CommunicationLogStatus::Failed ? now() : null,
            'recipients' => $recipients,
        ]);
    }

    public function recordFromNotification(ErpNotification $notification): CommunicationLog
    {
        $existing = CommunicationLog::query()
            ->where('source_type', ErpNotification::class)
            ->where('source_id', $notification->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $recipient = $notification->recipient;

        return $this->record([
            'company_id' => $notification->company_id,
            'channel' => CommunicationLogChannel::Notification,
            'communication_type' => CommunicationLogType::Alert,
            'subject' => $notification->title,
            'message_body' => $notification->body,
            'status' => CommunicationLogStatus::Delivered,
            'priority' => $notification->priority,
            'source_type' => ErpNotification::class,
            'source_id' => $notification->id,
            'created_by' => $notification->created_by,
            'sent_at' => $notification->created_at,
            'delivered_at' => $notification->created_at,
            'recipients' => [[
                'recipient_type' => 'user',
                'recipient_id' => $recipient?->id,
                'display_name' => $recipient?->name,
                'email' => $recipient?->email,
                'delivery_status' => CommunicationLogStatus::Delivered->value,
            ]],
        ]);
    }

    public function recordFromWhatsappMessage(WhatsappMessage $message): CommunicationLog
    {
        $conversation = $message->conversation;
        $participant = $conversation?->participants->first();

        $status = match ($message->status) {
            WhatsappDeliveryStatus::Delivered => CommunicationLogStatus::Delivered,
            WhatsappDeliveryStatus::Read => CommunicationLogStatus::Read,
            WhatsappDeliveryStatus::Sent => CommunicationLogStatus::Sent,
            WhatsappDeliveryStatus::Failed => CommunicationLogStatus::Failed,
            WhatsappDeliveryStatus::Cancelled => CommunicationLogStatus::Cancelled,
            WhatsappDeliveryStatus::Expired => CommunicationLogStatus::Failed,
            default => CommunicationLogStatus::Queued,
        };

        $type = match ($message->message_type) {
            WhatsappMessageType::Template => CommunicationLogType::Transactional,
            WhatsappMessageType::System => CommunicationLogType::System,
            default => CommunicationLogType::Operational,
        };

        $existing = CommunicationLog::query()
            ->where('source_type', WhatsappMessage::class)
            ->where('source_id', $message->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'sent_at' => $message->sent_at,
                'delivered_at' => $message->delivered_at,
                'failed_at' => $message->failed_at,
                'provider_response' => $message->provider_response,
            ]);
            $this->recordEvent($existing, $status->value, $status->value, ['whatsapp_message_id' => $message->id]);

            return $existing->fresh();
        }

        $recipients = [];
        if ($participant) {
            $recipients[] = [
                'recipient_type' => $participant->participant_type,
                'recipient_id' => $participant->participant_id,
                'display_name' => $participant->display_name,
                'phone' => $participant->phone_number ?? $conversation?->phone_number,
                'delivery_status' => $status->value,
            ];
        } elseif ($conversation) {
            $recipients[] = [
                'recipient_type' => $conversation->customer_id ? 'customer' : 'external',
                'recipient_id' => $conversation->customer_id,
                'phone' => $conversation->phone_number,
                'delivery_status' => $status->value,
            ];
        }

        return $this->record([
            'company_id' => $message->company_id,
            'branch_id' => $message->branch_id,
            'channel' => CommunicationLogChannel::WhatsApp,
            'communication_type' => $type,
            'subject' => $message->communicationTemplate?->name,
            'message_body' => $message->body,
            'status' => $status,
            'communication_template_id' => $message->communication_template_id,
            'template_code' => $message->communicationTemplate?->code,
            'source_type' => WhatsappMessage::class,
            'source_id' => $message->id,
            'created_by' => $message->created_by,
            'sent_by' => $message->created_by,
            'sent_at' => $message->sent_at,
            'delivered_at' => $message->delivered_at,
            'failed_at' => $message->failed_at,
            'provider_response' => $message->provider_response,
            'recipients' => $recipients,
        ]);
    }

    public function recordFromEmailMessage(EmailMessage $message): CommunicationLog
    {
        $status = match ($message->status) {
            EmailDeliveryStatus::Delivered => CommunicationLogStatus::Delivered,
            EmailDeliveryStatus::Opened => CommunicationLogStatus::Read,
            EmailDeliveryStatus::Clicked => CommunicationLogStatus::Read,
            EmailDeliveryStatus::Sent => CommunicationLogStatus::Sent,
            EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced => CommunicationLogStatus::Failed,
            EmailDeliveryStatus::Cancelled => CommunicationLogStatus::Cancelled,
            default => CommunicationLogStatus::Queued,
        };

        $existing = CommunicationLog::query()
            ->where('source_type', EmailMessage::class)
            ->where('source_id', $message->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'subject' => $message->subject,
                'sent_at' => $message->sent_at,
                'delivered_at' => $message->delivered_at,
                'failed_at' => $message->failed_at ?? $message->bounced_at,
                'read_at' => $message->opened_at,
                'provider_response' => $message->provider_response,
                'delivery_response' => ['failure_reason' => $message->failure_reason],
            ]);
            $this->recordEvent($existing, $status->value, $status->value, ['email_message_id' => $message->id]);

            return $existing->fresh();
        }

        $recipients = [];
        $recipient = $message->recipient;
        if ($recipient) {
            $recipients[] = [
                'recipient_type' => $recipient->source_type,
                'recipient_id' => $recipient->source_id,
                'display_name' => $recipient->display_name,
                'email' => $recipient->email,
                'delivery_status' => $status->value,
            ];
        } else {
            foreach ($message->to_emails ?? [] as $to) {
                $recipients[] = [
                    'recipient_type' => 'external',
                    'display_name' => $to['name'] ?? null,
                    'email' => $to['email'] ?? $to,
                    'delivery_status' => $status->value,
                ];
            }
        }

        $attachments = $message->attachments->map(fn ($a) => [
            'attachment_type' => $a->attachment_type->value,
            'attachable_type' => $a->attachable_type,
            'attachable_id' => $a->attachable_id,
            'label' => $a->label,
        ])->all();

        return $this->record([
            'company_id' => $message->company_id,
            'branch_id' => $message->branch_id,
            'channel' => CommunicationLogChannel::Email,
            'communication_type' => CommunicationLogType::Transactional,
            'subject' => $message->subject,
            'message_body' => $message->body,
            'status' => $status,
            'communication_template_id' => $message->communication_template_id,
            'template_code' => $message->communicationTemplate?->code,
            'source_type' => EmailMessage::class,
            'source_id' => $message->id,
            'created_by' => $message->created_by,
            'sent_by' => $message->created_by,
            'sent_at' => $message->sent_at,
            'delivered_at' => $message->delivered_at,
            'failed_at' => $message->failed_at ?? $message->bounced_at,
            'provider_response' => $message->provider_response,
            'delivery_response' => ['failure_reason' => $message->failure_reason],
            'recipients' => $recipients,
            'attachments' => $attachments,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = CommunicationLog::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['recipients', 'creator', 'sender', 'template', 'branch']);

        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['communication_type'])) {
            $query->where('communication_type', $filters['communication_type']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('reference_number', 'like', $term)
                    ->orWhere('subject', 'like', $term)
                    ->orWhere('message_body', 'like', $term)
                    ->orWhere('template_code', 'like', $term)
                    ->orWhereHas('recipients', function (Builder $r) use ($term) {
                        $r->where('display_name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        if (($filters['view'] ?? null) === 'failures') {
            $query->where('status', CommunicationLogStatus::Failed);
        }

        $sort = $filters['sort'] ?? 'newest';
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        return $query;
    }

    /**
     * @return Collection<int, CommunicationLog>
     */
    public function forEntity(
        string $recipientType,
        int $recipientId,
        int $companyId,
        int $limit = 50,
        ?CommunicationLogChannel $channel = null,
    ): Collection {
        return CommunicationLog::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->when($channel, fn (Builder $q) => $q->where('channel', $channel))
            ->whereHas('recipients', function (Builder $q) use ($recipientType, $recipientId) {
                $q->where('recipient_type', $recipientType)->where('recipient_id', $recipientId);
            })
            ->with(['recipients', 'creator', 'template'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function analytics(int $companyId): array
    {
        $base = CommunicationLog::query()->where('company_id', $companyId);

        $total = (clone $base)->count();
        $delivered = (clone $base)->whereIn('status', [
            CommunicationLogStatus::Delivered,
            CommunicationLogStatus::Sent,
            CommunicationLogStatus::Read,
        ])->count();
        $failed = (clone $base)->where('status', CommunicationLogStatus::Failed)->count();

        $byChannel = (clone $base)
            ->select('channel', DB::raw('COUNT(*) as total'))
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->all();

        $byBranch = (clone $base)
            ->whereNotNull('branch_id')
            ->selectRaw('branch_id, COUNT(*) as total')
            ->groupBy('branch_id')
            ->get()
            ->map(function ($row) {
                $branch = \App\Models\Branch::query()->find($row->branch_id);

                return ['branch' => $branch?->name ?? __('Unknown'), 'total' => (int) $row->total];
            });

        return [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'delivery_rate' => $total > 0 ? (int) round(($delivered / $total) * 100) : 0,
            'by_channel' => $byChannel,
            'by_branch' => $byBranch,
            'sent_today' => (clone $base)->whereDate('sent_at', today())->count(),
            'sent_month' => (clone $base)->where('sent_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function timelinePayload(Collection $logs): array
    {
        return $logs->map(fn (CommunicationLog $log) => [
            'id' => $log->id,
            'reference_number' => $log->reference_number,
            'channel' => $log->channel->value,
            'channel_label' => $log->channel->label(),
            'type' => $log->communication_type->value,
            'type_label' => $log->communication_type->label(),
            'status' => $log->status->value,
            'status_label' => $log->status->label(),
            'status_badge' => $log->status->badgeClass(),
            'priority' => $log->priority->value,
            'subject' => $log->subject,
            'message' => $log->message_body,
            'recipient' => $log->recipients->first()?->display_name
                ?? $log->recipients->first()?->phone
                ?? $log->recipients->first()?->email,
            'created_at' => $log->created_at?->toIso8601String(),
            'sent_at' => $log->sent_at?->toIso8601String(),
            'url' => route('admin.communications.logs.show', $log),
        ])->all();
    }

    protected function nextReference(int $companyId): string
    {
        $count = CommunicationLog::query()->where('company_id', $companyId)->count() + 1;

        return 'COM-'.now()->format('ymd').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
