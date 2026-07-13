@php
    $typeLabel = ucfirst(str_replace('_', ' ', $activity->activity_type->value));
    $assigneeName = $activity->user?->name ?? __('Unassigned');
    $assigneeInitials = collect(preg_split('/\s+/', trim($assigneeName)) ?: [])
        ->filter()
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->join('');
@endphp

<x-admin-layout
    :title="$activity->subject"
    :breadcrumbs="[
        ['label' => __('Commercial')],
        ['label' => __('CRM')],
        ['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')],
        ['label' => $activity->subject],
    ]"
>
    <div class="activity-show w-full min-w-0 space-y-4">
        <div class="activity-show__toolbar">
            <a
                href="{{ route('admin.commercial.activities.index') }}"
                class="activity-show__back"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('Back to Activities') }}
            </a>

            <div class="activity-show__toolbar-actions">
                @can('update', $activity)
                    <a href="{{ route('admin.commercial.activities.edit', $activity) }}" class="erp-btn-secondary erp-btn--sm">{{ __('Edit') }}</a>
                @endcan
                @if ($activity->customer)
                    <x-admin.customer-360-action :customer="$activity->customer" />
                @endif
            </div>
        </div>

        <section class="activity-show__hero" aria-labelledby="activity-show-title">
            @include('admin.commercial.activities.partials.type-icon', ['type' => $activity->activity_type])

            <div class="activity-show__hero-body">
                <div class="activity-show__hero-badges">
                    <span class="activity-show__type-chip">{{ $typeLabel }}</span>
                    <x-admin.enum-status-badge :status="$activity->status->value" />
                </div>

                <h1 id="activity-show-title" class="activity-show__title">{{ $activity->subject }}</h1>

                <div class="activity-show__hero-meta">
                    <time datetime="{{ $activity->activity_at->toIso8601String() }}" class="activity-show__when">
                        {{ $activity->activity_at->format('D, j M Y · H:i') }}
                    </time>
                    <span class="activity-show__meta-dot" aria-hidden="true">·</span>
                    <span class="activity-show__relative">{{ $activity->activity_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="activity-show__assignee" title="{{ $assigneeName }}">
                <span class="activity-show__assignee-avatar" aria-hidden="true">{{ $assigneeInitials ?: '?' }}</span>
                <div class="activity-show__assignee-copy">
                    <span class="activity-show__assignee-label">{{ __('Assigned to') }}</span>
                    <span class="activity-show__assignee-name">{{ $assigneeName }}</span>
                </div>
            </div>
        </section>

        <div class="activity-show__layout">
            <div class="activity-show__main">
                <section class="activity-show__panel">
                    <header class="activity-show__panel-head">
                        <h2 class="activity-show__panel-title">{{ __('Notes & description') }}</h2>
                    </header>
                    <div class="activity-show__panel-body">
                        @if (filled($activity->description))
                            <p class="activity-show__notes">{{ $activity->description }}</p>
                        @else
                            <p class="activity-show__empty">{{ __('No notes were added for this activity.') }}</p>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="activity-show__aside">
                @if ($activity->customer || $activity->lead)
                    <section class="activity-show__panel">
                        <header class="activity-show__panel-head">
                            <h2 class="activity-show__panel-title">{{ __('Related records') }}</h2>
                        </header>
                        <div class="activity-show__panel-body activity-show__links">
                            @if ($activity->customer)
                                <a
                                    href="{{ route('admin.crm.customers.show', $activity->customer) }}"
                                    class="activity-show__link-card"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >
                                    <span class="activity-show__link-icon activity-show__link-icon--customer" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </span>
                                    <span class="activity-show__link-copy">
                                        <span class="activity-show__link-label">{{ __('Customer') }}</span>
                                        <span class="activity-show__link-value">{{ $activity->customer->company_name }}</span>
                                    </span>
                                    <svg class="activity-show__link-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif

                            @if ($activity->lead)
                                <a
                                    href="{{ route('admin.crm.leads.show', $activity->lead) }}"
                                    class="activity-show__link-card"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >
                                    <span class="activity-show__link-icon activity-show__link-icon--lead" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </span>
                                    <span class="activity-show__link-copy">
                                        <span class="activity-show__link-label">{{ __('Lead') }}</span>
                                        <span class="activity-show__link-value">{{ $activity->lead->lead_name }}</span>
                                    </span>
                                    <svg class="activity-show__link-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="activity-show__panel">
                    <header class="activity-show__panel-head">
                        <h2 class="activity-show__panel-title">{{ __('Activity details') }}</h2>
                    </header>
                    <dl class="activity-show__details">
                        <div class="activity-show__detail">
                            <dt>{{ __('Type') }}</dt>
                            <dd>{{ $typeLabel }}</dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt>{{ __('Status') }}</dt>
                            <dd><x-admin.enum-status-badge :status="$activity->status->value" /></dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt>{{ __('When') }}</dt>
                            <dd>{{ $activity->activity_at->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt>{{ __('Assigned to') }}</dt>
                            <dd>{{ $assigneeName }}</dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt>{{ __('Logged') }}</dt>
                            <dd>{{ $activity->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        </div>
                        @if ($activity->updated_at && ! $activity->updated_at->equalTo($activity->created_at))
                            <div class="activity-show__detail">
                                <dt>{{ __('Last updated') }}</dt>
                                <dd>{{ $activity->updated_at->format('Y-m-d H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                @can('delete', $activity)
                    <section class="activity-show__panel activity-show__panel--danger">
                        <header class="activity-show__panel-head">
                            <h2 class="activity-show__panel-title">{{ __('Remove activity') }}</h2>
                        </header>
                        <div class="activity-show__panel-body">
                            <p class="activity-show__danger-copy">{{ __('This permanently removes the activity from customer and lead history.') }}</p>
                            <form
                                method="POST"
                                action="{{ route('admin.commercial.activities.destroy', $activity) }}"
                                onsubmit="return confirm(@js(__('Delete this activity?')))"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="erp-btn-secondary erp-btn--sm text-red-700 hover:border-red-200 hover:bg-red-50">
                                    {{ __('Delete activity') }}
                                </button>
                            </form>
                        </div>
                    </section>
                @endcan
            </aside>
        </div>
    </div>
</x-admin-layout>
