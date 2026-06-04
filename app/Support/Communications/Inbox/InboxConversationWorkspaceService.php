<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Enums\InboxSlaStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Enums\CommunicationTemplateStatus;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class InboxConversationWorkspaceService
{
    public const SUGGESTED_TAGS = [
        'urgent', 'vip', 'complaint', 'follow_up', 'artwork', 'production', 'payment', 'delivery',
    ];

    public function __construct(
        protected InboxTimelineService $timeline,
        protected InboxSlaService $sla,
        protected InboxChatFeedService $chatFeedService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forConversation(CommunicationConversation $conversation, ?string $channelFilter = null): array
    {
        $timeline = $this->timeline->build($conversation);
        $erpEvents = $this->erpEvents($conversation);
        $merged = $timeline->concat($erpEvents)->sortBy('at')->values();
        $filtered = $this->filterByChannel($merged, $channelFilter);

        return [
            'kpis' => $this->kpis($conversation),
            'sla_detail' => $this->slaDetail($conversation),
            'timeline' => $filtered,
            'message_timeline' => $this->chatFeedService->build($this->chatFeed($merged), $conversation),
            'media_library' => $this->chatFeedService->mediaLibrary($conversation),
            'channels_present' => $merged->pluck('channel')->filter()->unique()->values()->all(),
            'templates' => $this->messageTemplates($conversation->company_id),
            'suggested_tags' => self::SUGGESTED_TAGS,
            'mentionable_users' => User::query()
                ->where('company_id', $conversation->company_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return Collection<int, array<string, mixed>>
     */
    public function messagesOnly(Collection $events): Collection
    {
        return $events->filter(fn (array $event) => ($event['type'] ?? '') === 'message')->values();
    }

    /**
     * Messages and file attachments for the chat thread (WhatsApp-style feed).
     *
     * @param  Collection<int, array<string, mixed>>  $events
     * @return Collection<int, array<string, mixed>>
     */
    public function chatFeed(Collection $events): Collection
    {
        return $events
            ->filter(fn (array $event) => in_array($event['type'] ?? '', ['message', 'attachment'], true))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function kpis(CommunicationConversation $conversation): array
    {
        $metrics = $this->sla->metrics($conversation);
        $messageCount = $conversation->threadMessages->count();
        $ageMinutes = $conversation->started_at
            ? (int) $conversation->started_at->diffInMinutes(now())
            : null;

        return [
            'total_messages' => $messageCount,
            'conversation_age_minutes' => $ageMinutes,
            'conversation_age_label' => $this->formatDuration($ageMinutes),
            'first_response_minutes' => $metrics['first_response_minutes'],
            'last_response_minutes' => $metrics['last_response_minutes'],
            'avg_response_minutes' => $metrics['last_response_minutes'],
            'open_duration_minutes' => $metrics['open_duration_minutes'],
            'assigned_user' => $conversation->assignee?->name ?? __('Unassigned'),
            'status' => $conversation->status,
            'escalation_level' => $conversation->is_escalated ? __('Escalated') : __('Normal'),
            'unread_count' => $conversation->unread_count,
            'sla_status' => $metrics['sla_status'],
            'is_overdue' => $metrics['is_overdue'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function slaDetail(CommunicationConversation $conversation): array
    {
        $metrics = $this->sla->metrics($conversation);

        return [
            'first_response' => $this->slaState($metrics['first_response_minutes'], 60),
            'follow_up' => $this->slaState($metrics['waiting_duration_minutes'], 720),
            'resolution' => $this->slaState($metrics['open_duration_minutes'], 4320),
            'escalation' => $conversation->is_escalated
                ? InboxSlaStatus::Red
                : InboxSlaStatus::Green,
            'overall' => $metrics['sla_status'],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function erpEvents(CommunicationConversation $conversation): Collection
    {
        if (! $conversation->customer_id) {
            return collect();
        }

        $events = collect();
        $customerId = $conversation->customer_id;

        foreach (Quotation::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->created_at, __('Quotation created'), $row->quotation_number, route('admin.quotations.show', $row), 'quotation'));
        }

        foreach (SalesOrder::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->created_at, __('Sales order created'), $row->order_number, route('admin.sales-orders.show', $row), 'sales_order'));
        }

        foreach (ArtworkRequest::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->created_at, __('Artwork request'), $row->request_number, route('admin.artwork.show', $row), 'artwork'));
        }

        foreach (ProductionJobCard::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->created_at, __('Production job'), $row->job_card_number, route('admin.production.job-cards.show', $row), 'job'));
        }

        foreach (CustomerInvoice::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->created_at, __('Invoice generated'), $row->invoice_number, route('admin.invoices.show', $row), 'invoice'));
        }

        foreach (CustomerPayment::query()->where('customer_id', $customerId)->orderByDesc('created_at')->limit(5)->get() as $row) {
            $events->push($this->erpEvent($row->payment_date ?? $row->created_at, __('Payment received'), $row->payment_number, route('admin.payments.show', $row), 'payment'));
        }

        return $events;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return Collection<int, array<string, mixed>>
     */
    public function filterByChannel(Collection $events, ?string $channel): Collection
    {
        if (! $channel) {
            return $events;
        }

        return $events->filter(function (array $event) use ($channel) {
            $eventChannel = $event['channel'] ?? $event['erp_type'] ?? $event['type'] ?? '';

            return $eventChannel === $channel
                || ($channel === 'note' && ($event['type'] ?? '') === 'internal_note')
                || ($channel === 'system' && in_array($event['type'] ?? '', ['system', 'audit', 'erp'], true));
        })->values();
    }

    /**
     * @return Collection<int, CommunicationTemplate>
     */
    protected function messageTemplates(int $companyId): Collection
    {
        return CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('status', CommunicationTemplateStatus::Active)
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'code', 'channel', 'body']);
    }

    protected function erpEvent(mixed $at, string $title, string $body, string $url, string $type): array
    {
        return [
            'at' => $at,
            'type' => 'erp',
            'channel' => 'erp',
            'erp_type' => $type,
            'title' => $title,
            'body' => $body,
            'meta' => null,
            'url' => $url,
            'icon' => $type,
        ];
    }

    protected function slaState(?int $minutes, int $amberAfter): InboxSlaStatus
    {
        if ($minutes === null) {
            return InboxSlaStatus::Green;
        }

        if ($minutes >= $amberAfter * 2) {
            return InboxSlaStatus::Red;
        }

        if ($minutes >= $amberAfter) {
            return InboxSlaStatus::Amber;
        }

        return InboxSlaStatus::Green;
    }

    protected function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        if ($minutes < 60) {
            return $minutes.'m';
        }

        if ($minutes < 1440) {
            return round($minutes / 60, 1).'h';
        }

        return round($minutes / 1440, 1).'d';
    }
}
