<?php

namespace App\Support\Communications\Inbox;

use App\Enums\CommunicationLogChannel;
use App\Enums\InboxMessageChannel;
use App\Enums\InboxMessageStatus;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationMessage;

class InboxLogSyncService
{
    /**
     * Import COM-4 communication logs into the unified thread (idempotent).
     */
    public function syncCommunicationLogs(CommunicationConversation $conversation): int
    {
        $query = CommunicationLog::query()
            ->where('company_id', $conversation->company_id)
            ->orderBy('created_at');

        if ($conversation->customer_id) {
            $query->whereHas('recipients', function ($q) use ($conversation) {
                $q->where('recipient_type', 'customer')->where('recipient_id', $conversation->customer_id);
            });
        } else {
            return 0;
        }

        $imported = 0;
        foreach ($query->get() as $log) {
            if (CommunicationConversationMessage::query()
                ->where('communication_log_id', $log->id)
                ->exists()) {
                continue;
            }

            $channel = match ($log->channel) {
                CommunicationLogChannel::WhatsApp => InboxMessageChannel::WhatsApp,
                CommunicationLogChannel::Sms => InboxMessageChannel::Sms,
                CommunicationLogChannel::Email => InboxMessageChannel::Email,
                default => InboxMessageChannel::InApp,
            };

            CommunicationConversationMessage::query()->create([
                'communication_conversation_id' => $conversation->id,
                'company_id' => $conversation->company_id,
                'channel' => $channel,
                'direction' => 'outgoing',
                'message_type' => 'message',
                'body' => $log->message_body,
                'status' => InboxMessageStatus::Delivered,
                'source_type' => CommunicationLog::class,
                'source_id' => $log->id,
                'communication_log_id' => $log->id,
                'created_by' => $log->created_by,
                'sent_at' => $log->sent_at ?? $log->created_at,
                'delivered_at' => $log->delivered_at,
                'read_at' => $log->read_at,
            ]);
            $imported++;
        }

        if ($imported > 0) {
            $last = $conversation->threadMessages()->orderByDesc('created_at')->first();
            if ($last) {
                app(InboxConversationService::class)->touchActivity(
                    $conversation,
                    $last->body,
                    $last->channel->value,
                );
            }
        }

        return $imported;
    }
}
