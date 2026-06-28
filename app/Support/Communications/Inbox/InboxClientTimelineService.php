<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxMessageStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use Illuminate\Support\Collection;

class InboxClientTimelineService
{
    public function __construct(
        protected InboxLogSyncService $sync,
    ) {}

    /**
     * Client-safe timeline: messages and attachments only (no internal notes or audit).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function build(CommunicationConversation $conversation): Collection
    {
        $this->sync->syncCommunicationLogs($conversation);

        $events = collect();

        foreach ($conversation->threadMessages as $msg) {
            if ($msg->status === InboxMessageStatus::Archived) {
                continue;
            }

            $events->push([
                'at' => $msg->created_at,
                'type' => 'message',
                'message_id' => $msg->id,
                'status' => $msg->status->value,
                'channel' => $msg->channel->value,
                'direction' => $msg->direction,
                'title' => $msg->direction === 'outgoing' ? __('Jana Prints') : __('You'),
                'body' => $msg->body,
                'meta' => $msg->direction === 'outgoing' ? ($msg->creator?->name ?? __('Team')) : null,
                'icon' => $msg->channel->value,
            ]);
        }

        foreach ($conversation->attachments->whereNull('archived_at') as $attachment) {
            $linked = $attachment->communication_conversation_message_id
                ? $conversation->threadMessages->firstWhere('id', $attachment->communication_conversation_message_id)
                : null;
            $direction = $linked?->direction ?? ($attachment->uploaded_by ? 'incoming' : 'outgoing');

            $events->push([
                'at' => $attachment->created_at,
                'type' => 'attachment',
                'direction' => $direction,
                'title' => __('Attachment'),
                'body' => $attachment->label ?? basename((string) $attachment->file_path),
                'meta' => null,
                'attachment_id' => $attachment->id,
                'message_id' => $attachment->communication_conversation_message_id,
                'attachment_type' => $attachment->attachment_type,
                'file_url' => $attachment->previewUrl(),
                'is_image' => $attachment->isImage(),
                'download_url' => route('client.communications.attachments.download', [
                    'conversation' => $conversation->id,
                    'attachment' => $attachment->id,
                ]),
            ]);
        }

        return $events->sortBy('at')->values();
    }
}
