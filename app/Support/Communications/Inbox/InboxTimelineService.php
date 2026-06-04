<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxAuditEventType;
use App\Enums\InboxMessageStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use Illuminate\Support\Collection;

class InboxTimelineService
{
    public function __construct(
        protected InboxLogSyncService $sync,
    ) {}

    /**
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
                'title' => $msg->direction === 'outgoing' ? __('Staff reply') : __('Customer message'),
                'body' => $msg->body,
                'meta' => $msg->creator?->name,
                'icon' => $msg->channel->value,
            ]);
        }

        foreach ($conversation->attachments->whereNull('archived_at') as $attachment) {
            $events->push([
                'at' => $attachment->created_at,
                'type' => 'attachment',
                'direction' => 'outgoing',
                'title' => __('Attachment'),
                'body' => $attachment->label ?? basename((string) $attachment->file_path),
                'meta' => $attachment->uploader?->name,
                'attachment_id' => $attachment->id,
                'message_id' => $attachment->communication_conversation_message_id,
                'attachment_type' => $attachment->attachment_type,
                'file_url' => $attachment->previewUrl(),
                'is_image' => $attachment->isImage(),
                'download_url' => route('admin.communications.inbox.attachments.download', [
                    'inboxConversation' => $conversation->id,
                    'attachment' => $attachment->id,
                ]),
            ]);
        }

        foreach ($conversation->notes()->orderBy('created_at')->get() as $note) {
            $events->push([
                'at' => $note->created_at,
                'type' => 'internal_note',
                'title' => __('Internal note'),
                'body' => $note->body,
                'meta' => $note->author?->name,
                'tags' => $note->tags ?? [],
            ]);
        }

        foreach ($conversation->statusHistory as $hist) {
            $events->push([
                'at' => $hist->created_at,
                'type' => 'system',
                'title' => __('Status changed'),
                'body' => ($hist->from_status ? $hist->from_status.' → ' : '').$hist->to_status,
                'meta' => $hist->creator?->name,
            ]);
        }

        foreach ($conversation->assignments as $asgn) {
            $events->push([
                'at' => $asgn->created_at,
                'type' => 'system',
                'title' => $asgn->action->label(),
                'body' => trim(($asgn->fromUser?->name ?? __('—')).' → '.($asgn->toUser?->name ?? __('—'))),
                'meta' => $asgn->creator?->name ?? null,
            ]);
        }

        foreach ($conversation->auditEvents as $audit) {
            if ($audit->event_type === InboxAuditEventType::NoteAdded) {
                continue;
            }
            $events->push([
                'at' => $audit->created_at,
                'type' => 'audit',
                'title' => $audit->event_type->label(),
                'body' => $audit->payload['summary'] ?? '',
                'meta' => $audit->user?->name,
            ]);
        }

        return $events->sortBy('at')->values();
    }
}
