<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Enums\InboxSlaStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class InboxSlaService
{
    public const FIRST_RESPONSE_TARGET_MINUTES = 60;

    public const OVERDUE_WAIT_HOURS = 24;

    public function evaluate(CommunicationConversation $conversation): InboxSlaStatus
    {
        if (in_array($conversation->status, [InboxConversationStatus::Closed, InboxConversationStatus::Archived, InboxConversationStatus::Resolved], true)) {
            return InboxSlaStatus::Green;
        }

        if ($conversation->is_escalated) {
            return InboxSlaStatus::Red;
        }

        $waitingSince = $conversation->waiting_since;
        if ($waitingSince && $waitingSince->lte(now()->subHours(self::OVERDUE_WAIT_HOURS))) {
            return InboxSlaStatus::Red;
        }

        if (! $conversation->first_response_at && $conversation->started_at) {
            $minutes = $conversation->started_at->diffInMinutes(now());
            if ($minutes >= self::FIRST_RESPONSE_TARGET_MINUTES) {
                return InboxSlaStatus::Red;
            }
            if ($minutes >= (self::FIRST_RESPONSE_TARGET_MINUTES / 2)) {
                return InboxSlaStatus::Amber;
            }
        }

        if ($waitingSince && $waitingSince->lte(now()->subHours(12))) {
            return InboxSlaStatus::Amber;
        }

        return InboxSlaStatus::Green;
    }

    public function refresh(CommunicationConversation $conversation): InboxSlaStatus
    {
        $status = $this->evaluate($conversation);
        if ($conversation->sla_status !== $status) {
            $conversation->update(['sla_status' => $status]);
        }

        return $status;
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(CommunicationConversation $conversation): array
    {
        $firstResponse = $this->minutesBetween($conversation->started_at, $conversation->first_response_at);
        $lastResponse = $this->minutesBetween($conversation->last_customer_message_at, $conversation->last_staff_response_at);
        $openDuration = $conversation->started_at
            ? $conversation->started_at->diffInMinutes($conversation->closed_at ?? now())
            : null;
        $waitingDuration = $conversation->waiting_since
            ? $conversation->waiting_since->diffInMinutes(now())
            : null;
        $escalationDuration = $conversation->escalated_at
            ? $conversation->escalated_at->diffInMinutes(now())
            : null;

        return [
            'sla_status' => $this->refresh($conversation),
            'first_response_minutes' => $firstResponse,
            'last_response_minutes' => $lastResponse,
            'open_duration_minutes' => $openDuration,
            'waiting_duration_minutes' => $waitingDuration,
            'escalation_duration_minutes' => $escalationDuration,
            'is_overdue' => $this->evaluate($conversation) === InboxSlaStatus::Red,
        ];
    }

    protected function minutesBetween(mixed $from, mixed $to): ?int
    {
        $from = $this->asCarbon($from);
        $to = $this->asCarbon($to);

        if (! $from || ! $to) {
            return null;
        }

        return (int) $from->diffInMinutes($to);
    }

    protected function asCarbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    public function recordStaffResponse(CommunicationConversation $conversation): void
    {
        $now = now();
        $updates = ['last_staff_response_at' => $now, 'waiting_since' => $now];
        if (! $conversation->first_response_at) {
            $updates['first_response_at'] = $now;
        }
        $conversation->update($updates);
        $this->refresh($conversation);
    }
}
