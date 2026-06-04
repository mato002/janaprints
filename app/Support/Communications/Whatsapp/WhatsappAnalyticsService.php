<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappConversationStatus;
use App\Enums\WhatsappDeliveryStatus;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappMessage;

class WhatsappAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $companyId): array
    {
        $messages = WhatsappMessage::query()->where('company_id', $companyId);
        $total = (clone $messages)->count();
        $sent = (clone $messages)->whereIn('status', [
            WhatsappDeliveryStatus::Sent,
            WhatsappDeliveryStatus::Delivered,
            WhatsappDeliveryStatus::Read,
        ])->count();
        $failed = (clone $messages)->where('status', WhatsappDeliveryStatus::Failed)->count();
        $queued = (clone $messages)->where('status', WhatsappDeliveryStatus::Queued)->count();

        $conversations = WhatsappConversation::query()->where('company_id', $companyId);
        $open = (clone $conversations)->where('status', WhatsappConversationStatus::Open)->count();
        $unread = (clone $conversations)->sum('unread_count');

        return [
            'total_messages' => $total,
            'sent_messages' => $sent,
            'failed_messages' => $failed,
            'queued_messages' => $queued,
            'delivery_rate' => $total > 0 ? (int) round(($sent / $total) * 100) : 0,
            'open_conversations' => $open,
            'unread_total' => (int) $unread,
            'sent_today' => (clone $messages)->whereDate('created_at', today())->count(),
            'sent_month' => (clone $messages)->where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }
}
