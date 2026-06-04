@php
    $events = $tabData['events'] ?? null;
    $filter = $tabData['filter'] ?? 'all';
    $search = $tabData['search'] ?? '';
    $filters = $tabData['filters'] ?? [];
    $accountingPlaceholder = $tabData['accounting_placeholder'] ?? false;
@endphp

<div class="c360-timeline">
    <div class="c360-timeline__intro mb-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Unified customer timeline') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Chronological audit trail across CRM, sales, artwork, production, and delivery.') }}</p>
    </div>

    <form
        method="GET"
        action="{{ route('admin.crm.customers.show', $customer) }}"
        class="c360-timeline__toolbar mb-4 space-y-3"
        data-turbo-frame="erp-main"
    >
        <input type="hidden" name="tab" value="timeline">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
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

        <div class="c360-timeline__filters flex flex-wrap gap-1.5" role="tablist" aria-label="{{ __('Timeline filters') }}">
            @foreach ($filters as $option)
                @php($active = $filter === $option['value'])
                <a
                    href="{{ route('admin.crm.customers.show', array_filter([
                        'customer' => $customer,
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

    @if ($accountingPlaceholder)
        <x-admin.card class="mb-4 border-dashed">
            <p class="text-sm text-slate-600">{{ __('Available after Accounting Activation') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Accounting events will appear in this feed when the module is enabled.') }}</p>
        </x-admin.card>
    @endif

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
