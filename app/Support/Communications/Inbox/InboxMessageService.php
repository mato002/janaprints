<?php

namespace App\Support\Communications\Inbox;

use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\InboxConversationStatus;
use App\Enums\InboxMessageChannel;
use App\Enums\InboxMessageStatus;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationAttachment;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Services\Communications\Inbox\InboxIncomingNotificationService;
use App\Support\Communications\CommunicationLogService;

class InboxMessageService
{
    public function __construct(
        protected InboxConversationService $conversations,
        protected CommunicationLogService $communicationLogs,
        protected InboxSlaService $sla,
        protected InboxAuditService $audit,
    ) {}

    public function reply(
        CommunicationConversation $conversation,
        string $body,
        int $userId,
        InboxMessageChannel $channel = InboxMessageChannel::InApp,
    ): CommunicationConversationMessage {
        $message = CommunicationConversationMessage::query()->create([
            'communication_conversation_id' => $conversation->id,
            'company_id' => $conversation->company_id,
            'channel' => $channel,
            'direction' => 'outgoing',
            'message_type' => 'message',
            'body' => $body,
            'status' => InboxMessageStatus::Sent,
            'created_by' => $userId,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);

        $log = $this->communicationLogs->record([
            'company_id' => $conversation->company_id,
            'branch_id' => $conversation->branch_id,
            'channel' => $channel->toLogChannel(),
            'communication_type' => CommunicationLogType::Operational,
            'subject' => $conversation->display_name,
            'message_body' => $body,
            'status' => CommunicationLogStatus::Sent,
            'created_by' => $userId,
            'sent_by' => $userId,
            'sent_at' => now(),
            'delivered_at' => now(),
            'recipients' => $this->recipientPayload($conversation),
        ]);

        $message->update(['communication_log_id' => $log->id, 'source_type' => CommunicationLog::class, 'source_id' => $log->id]);

        $this->conversations->touchActivity($conversation, $body, $channel->value);
        $this->sla->recordStaffResponse($conversation);
        $this->audit->record($conversation, \App\Enums\InboxAuditEventType::MessageSent, $userId, [
            'summary' => mb_substr($body, 0, 120),
            'channel' => $channel->value,
        ]);

        if ($conversation->status === InboxConversationStatus::WaitingCustomer) {
            $conversation->update(['status' => InboxConversationStatus::Open]);
        }

        return $message->fresh();
    }

    public function receiveFromCustomer(
        CommunicationConversation $conversation,
        string $body,
        ?int $portalUserId = null,
    ): CommunicationConversationMessage {
        $message = CommunicationConversationMessage::query()->create([
            'communication_conversation_id' => $conversation->id,
            'company_id' => $conversation->company_id,
            'channel' => InboxMessageChannel::InApp,
            'direction' => 'incoming',
            'message_type' => 'message',
            'body' => $body,
            'status' => InboxMessageStatus::Delivered,
            'created_by' => $portalUserId,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);

        $conversation->increment('unread_count');
        $conversation->update([
            'last_customer_message_at' => now(),
            'last_activity_at' => now(),
            'waiting_since' => now(),
            'status' => in_array($conversation->status, [
                InboxConversationStatus::Closed,
                InboxConversationStatus::Resolved,
                InboxConversationStatus::WaitingCustomer,
            ], true) ? InboxConversationStatus::Open : $conversation->status,
        ]);

        $this->conversations->touchActivity($conversation, $body, InboxMessageChannel::InApp->value);

        app(InboxIncomingNotificationService::class)->notify($conversation, $message);

        return $message->fresh();
    }

    public function storeClientAttachment(
        CommunicationConversation $conversation,
        CommunicationConversationAttachment $attachment,
        ?string $caption,
        int $portalUserId,
    ): CommunicationConversationMessage {
        $body = trim((string) $caption);
        $preview = $body !== '' ? $body : ($attachment->label ?? __('Attachment'));

        $message = $this->receiveFromCustomer(
            $conversation,
            $body !== '' ? $body : $preview,
            $portalUserId,
        );

        $attachment->update([
            'communication_conversation_message_id' => $message->id,
            'uploaded_by' => $portalUserId,
        ]);

        return $message->fresh();
    }

    public function archiveMessage(CommunicationConversationMessage $message, int $userId): void
    {
        $message->update(['status' => InboxMessageStatus::Archived]);
        $conversation = $message->conversation;

        $this->audit->record($conversation, \App\Enums\InboxAuditEventType::MessageDeleted, $userId, [
            'summary' => mb_substr((string) $message->body, 0, 120),
            'message_id' => $message->id,
        ]);
    }

    public function archiveAttachment(CommunicationConversationAttachment $attachment, int $userId): void
    {
        $attachment->update(['archived_at' => now()]);
        $conversation = $attachment->conversation;

        if ($attachment->communication_conversation_message_id) {
            CommunicationConversationMessage::query()
                ->where('id', $attachment->communication_conversation_message_id)
                ->update(['status' => InboxMessageStatus::Archived]);
        }

        $this->audit->record($conversation, \App\Enums\InboxAuditEventType::AttachmentRemoved, $userId, [
            'summary' => $attachment->label ?? basename((string) $attachment->file_path),
            'attachment_id' => $attachment->id,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recipientPayload(CommunicationConversation $conversation): array
    {
        if ($conversation->customer_id) {
            return [[
                'recipient_type' => 'customer',
                'recipient_id' => $conversation->customer_id,
                'display_name' => $conversation->display_name,
                'phone' => $conversation->phone_number,
                'email' => $conversation->email,
            ]];
        }

        return [[
            'recipient_type' => 'external',
            'display_name' => $conversation->display_name,
            'phone' => $conversation->phone_number,
            'email' => $conversation->email,
        ]];
    }
}
