<?php

namespace App\Services\Commercial;

use App\Enums\PublicQuoteRequestStatus;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PublicQuoteRequestCountService
{
    /**
     * Statuses that represent unreviewed / actionable intake (not quoted, closed, or rejected).
     *
     * @return list<PublicQuoteRequestStatus>
     */
    public function pendingStatuses(): array
    {
        return [
            PublicQuoteRequestStatus::Pending,
            PublicQuoteRequestStatus::Reviewing,
        ];
    }

    public function canView(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        foreach ([
            'public_leads.quote_requests.view',
            'quotations.view',
            'crm.customers.view',
            'crm.leads.view',
        ] as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function pendingCount(): int
    {
        return $this->pendingQuery()->count();
    }

    public function todayCount(): int
    {
        return $this->pendingQuery()
            ->whereDate('created_at', today())
            ->count();
    }

    public function unreadCount(): int
    {
        return $this->pendingCount();
    }

    public function latestPending(): ?PublicQuoteRequest
    {
        return $this->pendingQuery()->latest('created_at')->first();
    }

    /**
     * @return array{
     *     count: int,
     *     today_count: int,
     *     has_action: bool,
     *     route: string|null,
     *     label: string,
     *     subtext: string,
     *     cta: string,
     * }
     */
    public function alertPayload(): array
    {
        $count = $this->pendingCount();

        return [
            'count' => $count,
            'today_count' => $this->todayCount(),
            'has_action' => $count > 0,
            'route' => route('admin.public-quote-requests.index'),
            'label' => __('New Quote Requests'),
            'subtext' => $count > 0
                ? __('Potential customer inquiries awaiting review.')
                : __('No new quote requests'),
            'cta' => __('Review Requests'),
        ];
    }

    /**
     * @return array{count: int, route: string|null, visible: bool}
     */
    public function topbarPayload(): array
    {
        return [
            'count' => $this->pendingCount(),
            'route' => route('admin.public-quote-requests.index'),
            'visible' => $this->canView(),
            'label' => __('Quote Requests'),
        ];
    }

    protected function pendingQuery(): Builder
    {
        return PublicQuoteRequest::query()
            ->whereIn('status', array_map(
                fn (PublicQuoteRequestStatus $status) => $status->value,
                $this->pendingStatuses(),
            ));
    }
}
