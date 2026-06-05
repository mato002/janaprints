<?php

namespace App\Support\Commercial;

use App\Enums\CommercialTicketPriority;
use App\Enums\CommercialTicketStatus;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Commercial\CommercialTicketComment;
use App\Models\Commercial\CommercialTicketSlaEvent;
use Illuminate\Support\Carbon;

class SupportTicketService
{
    public function nextTicketNumber(int $companyId): string
    {
        $count = CommercialSupportTicket::query()
            ->where('company_id', $companyId)
            ->count() + 1;

        return 'TKT-'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public function defaultDueAt(CommercialTicketPriority $priority): Carbon
    {
        return now()->addHours($priority->defaultDueHours());
    }

    public function assign(CommercialSupportTicket $ticket, int $userId): CommercialSupportTicket
    {
        $status = in_array($ticket->status, [CommercialTicketStatus::Open, CommercialTicketStatus::Reopened], true)
            ? CommercialTicketStatus::Assigned
            : $ticket->status;

        $ticket->update([
            'assigned_to' => $userId,
            'status' => $status,
        ]);

        $this->recordSlaEvent($ticket, 'assigned', __('Ticket assigned.'));

        return $ticket->fresh();
    }

    public function addComment(
        CommercialSupportTicket $ticket,
        int $userId,
        string $comment,
        string $visibility = 'internal',
    ): CommercialTicketComment {
        return CommercialTicketComment::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $comment,
            'visibility' => $visibility,
        ]);
    }

    public function transition(CommercialSupportTicket $ticket, CommercialTicketStatus $status, ?string $notes = null): CommercialSupportTicket
    {
        abort_unless($ticket->status->canTransitionTo($status), 422, __('Invalid status transition.'));

        $payload = ['status' => $status];

        if ($status === CommercialTicketStatus::Resolved) {
            $payload['resolved_at'] = now();
        }

        if ($status === CommercialTicketStatus::Closed) {
            $payload['closed_at'] = now();
        }

        if ($status === CommercialTicketStatus::Reopened) {
            $payload['resolved_at'] = null;
            $payload['closed_at'] = null;
        }

        $ticket->update($payload);
        $this->recordSlaEvent($ticket, $status->value, $notes);

        return $ticket->fresh();
    }

    public function recordSlaEvent(CommercialSupportTicket $ticket, string $eventType, ?string $notes = null, ?int $userId = null): CommercialTicketSlaEvent
    {
        return CommercialTicketSlaEvent::query()->create([
            'ticket_id' => $ticket->id,
            'event_type' => $eventType,
            'event_at' => now(),
            'notes' => $notes,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }
}
