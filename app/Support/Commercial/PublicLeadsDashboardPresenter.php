<?php

namespace App\Support\Commercial;

use App\Enums\PublicContactMessageStatus;
use App\Models\PublicContactMessage;
use App\Services\Commercial\PublicQuoteRequestCountService;

class PublicLeadsDashboardPresenter
{
    public function __construct(
        protected PublicQuoteRequestCountService $quoteCounts,
    ) {}

    /**
     * @return array<string, int>
     */
    public function widgets(): array
    {
        return [
            'new_quote_requests' => $this->quoteCounts->todayCount(),
            'pending_quote_requests' => $this->quoteCounts->pendingCount(),
            'unread_contact_messages' => PublicContactMessage::query()
                ->where('status', PublicContactMessageStatus::Unread)
                ->count(),
            'new_contact_messages' => PublicContactMessage::query()
                ->where('status', PublicContactMessageStatus::Unread)
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function attentionCounts(): array
    {
        return [
            'pending_quote_requests' => $this->quoteCounts->pendingCount(),
            'unread_contact_messages' => PublicContactMessage::query()
                ->where('status', PublicContactMessageStatus::Unread)
                ->count(),
        ];
    }
}
