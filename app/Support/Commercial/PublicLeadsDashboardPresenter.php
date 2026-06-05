<?php

namespace App\Support\Commercial;

use App\Enums\PublicContactMessageStatus;
use App\Enums\PublicQuoteRequestStatus;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;

class PublicLeadsDashboardPresenter
{
    /**
     * @return array<string, int>
     */
    public function widgets(): array
    {
        return [
            'new_quote_requests' => PublicQuoteRequest::query()
                ->where('status', PublicQuoteRequestStatus::Pending)
                ->whereDate('created_at', today())
                ->count(),
            'pending_quote_requests' => PublicQuoteRequest::query()
                ->where('status', PublicQuoteRequestStatus::Pending)
                ->count(),
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
            'pending_quote_requests' => PublicQuoteRequest::query()
                ->where('status', PublicQuoteRequestStatus::Pending)
                ->count(),
            'unread_contact_messages' => PublicContactMessage::query()
                ->where('status', PublicContactMessageStatus::Unread)
                ->count(),
        ];
    }
}
