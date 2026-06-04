<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxMessageStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use Illuminate\Support\Collection;

class InboxChatFeedService
{
    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return Collection<int, array<string, mixed>>
     */
    public function build(Collection $events, CommunicationConversation $conversation): Collection
    {
        $messagesById = $conversation->threadMessages
            ->keyBy('id');

        $skipMessageIds = [];
        $enriched = collect();

        foreach ($events as $event) {
            $type = $event['type'] ?? '';

            if ($type === 'message' && ! empty($event['message_id']) && in_array((int) $event['message_id'], $skipMessageIds, true)) {
                continue;
            }

            if ($type === 'attachment') {
                $messageId = $event['message_id'] ?? null;
                if ($messageId && $messagesById->has($messageId)) {
                    /** @var CommunicationConversationMessage $linked */
                    $linked = $messagesById->get($messageId);
                    $event['caption'] = trim((string) $linked->body);
                    $event['channel'] = $linked->channel->value;
                    $skipMessageIds[] = (int) $messageId;
                }
                $event['dom_id'] = 'chat-att-'.$event['attachment_id'];
            }

            if ($type === 'message') {
                $event['dom_id'] = 'chat-msg-'.($event['message_id'] ?? uniqid());
                $event['can_manage'] = ($event['direction'] ?? '') === 'outgoing'
                    && ($event['status'] ?? '') !== InboxMessageStatus::Archived->value;
            }

            $enriched->push($event);
        }

        return $enriched->values();
    }

    /**
     * @return array{images: int, files: int, by_month: Collection<string, Collection<int, array<string, mixed>>>}
     */
    public function mediaLibrary(CommunicationConversation $conversation): array
    {
        $items = $conversation->attachments
            ->filter(fn ($att) => $att->archived_at === null && $att->file_path)
            ->sortByDesc('created_at')
            ->map(fn ($att) => [
                'id' => $att->id,
                'label' => $att->label,
                'is_image' => $att->isImage(),
                'file_url' => $att->previewUrl(),
                'download_url' => route('admin.communications.inbox.attachments.download', [
                    'inboxConversation' => $conversation->id,
                    'attachment' => $att->id,
                ]),
                'at' => $att->created_at,
                'month_key' => $att->created_at->format('Y-m'),
                'month_label' => $att->created_at->format('F Y'),
                'dom_id' => 'chat-att-'.$att->id,
                'message_id' => $att->communication_conversation_message_id,
            ]);

        $images = $items->filter(fn (array $i) => $i['is_image'])->values();
        $files = $items->reject(fn (array $i) => $i['is_image'])->values();

        return [
            'images' => $images->count(),
            'files' => $files->count(),
            'items' => $items->values(),
            'by_month' => $items->groupBy('month_key')->map(fn ($group, $key) => $group->values()),
        ];
    }
}
