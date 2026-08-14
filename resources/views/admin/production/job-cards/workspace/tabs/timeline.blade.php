@php
    $events = $tabData['events'] ?? null;
    $filter = $tabData['filter'] ?? 'all';
    $search = $tabData['search'] ?? '';
    $filters = $tabData['filters'] ?? [];
@endphp

<div class="c360-timeline">
    <div class="c360-timeline__intro mb-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Unified job timeline') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Chronological audit trail across traceability, operations, materials, quality, dispatch, and communications.') }}</p>
    </div>

    @if (! empty($tabData['communications']))
        <x-admin.card class="mb-4">
            <h4 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Email communications') }}</h4>
            <x-admin.customer-timeline-feed :events="$tabData['communications']" />
        </x-admin.card>
    @endif

    <form
        method="GET"
        action="{{ route('admin.production.job-cards.show', $jobCard) }}"
        class="c360-timeline__toolbar mb-4 space-y-3"
        data-turbo-frame="erp-main"
    >
        <input type="hidden" name="tab" value="timeline">

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1 max-w-xl">
                <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="timeline_search"
                    value="{{ $search }}"
                    class="erp-input w-full py-2 pl-9 text-sm"
                    placeholder="{{ __('Search timeline…') }}"
                    aria-label="{{ __('Search timeline') }}"
                />
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply') }}</button>
        </div>

        <div class="c360-timeline__filters flex flex-nowrap gap-1.5 overflow-x-auto" role="tablist" aria-label="{{ __('Timeline filters') }}">
            @foreach ($filters as $option)
                @php($active = $filter === $option['value'])
                <a
                    href="{{ route('admin.production.job-cards.show', array_filter([
                        'jobCard' => $jobCard,
                        'tab' => 'timeline',
                        'timeline_filter' => $option['value'] !== 'all' ? $option['value'] : null,
                        'timeline_search' => $search ?: null,
                    ])) }}"
                    class="erp-filter-pill {{ $active ? 'erp-filter-pill--active' : '' }}"
                    data-turbo-frame="erp-main"
                    @if ($active) aria-current="true" @endif
                >{{ $option['label'] }}</a>
            @endforeach
        </div>
    </form>

    <x-admin.card :padding="false" class="overflow-hidden">
        <div class="c360-timeline__feed px-4 py-3">
            @if ($events)
                <x-admin.customer-timeline-feed :events="$events" />
            @endif
        </div>
        @if ($events && $events->hasPages())
            <div class="border-t border-erp-border px-4 py-3">
                {{ $events->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
</div>
