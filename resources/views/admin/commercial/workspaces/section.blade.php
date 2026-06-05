<x-admin-layout :title="$workspace['title']">
    <x-admin.page-header
        :title="$workspace['title']"
        :description="$workspace['description']"
    />

    @if (! empty($quickActions))
        <x-admin.card class="mb-6">
            <div class="flex flex-wrap items-center gap-2 p-4">
                @foreach ($quickActions as $action)
                    @if (! empty($action['disabled']) || empty($action['href']))
                        <span class="erp-btn-secondary cursor-not-allowed opacity-60" title="{{ $action['hint'] ?? __('Coming soon') }}">{{ $action['label'] }}</span>
                    @else
                        <a href="{{ $action['href'] }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                    @endif
                @endforeach
            </div>
            @if (! empty($sectionNote))
                <p class="border-t border-erp-border px-4 py-2 text-xs text-slate-500">{{ __($sectionNote) }}</p>
            @endif
        </x-admin.card>
    @endif

    @if (! empty($widgets))
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
            <x-admin.kpi-widget :label="__('New leads today')" :value="$widgets['new_leads_today']" icon="sparkles" />
            <x-admin.kpi-widget :label="__('Open leads')" :value="$widgets['open_leads']" icon="collection" />
            <x-admin.kpi-widget :label="__('Active customers')" :value="$widgets['active_customers']" icon="user-circle" />
            <x-admin.kpi-widget :label="__('Quotes pending approval')" :value="$widgets['quotes_pending_approval']" icon="document-text" />
            <x-admin.kpi-widget :label="__('Artwork awaiting approval')" :value="$widgets['artwork_awaiting_approval']" icon="color-swatch" />
            <x-admin.kpi-widget :label="__('Orders ready for production')" :value="$widgets['sales_orders_ready']" icon="clipboard-list" />
            @isset($widgets['pos_sales_today'])
                <x-admin.kpi-widget :label="__('POS sales today')" :value="$widgets['pos_sales_today']" icon="cash" />
            @endisset
        </div>
    @endif

    <div
        x-data="workspaceHub(@js($cards))"
        x-cloak
        class="workspace-hub w-full min-w-0"
    >
        <div class="relative mb-4">
            <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
                type="search"
                x-model.debounce.150ms="query"
                @input="syncVisibleCards()"
                class="erp-input w-full py-2 pl-9 text-sm"
                placeholder="{{ __('Search in :workspace…', ['workspace' => $workspace['title']]) }}"
                aria-label="{{ __('Search workspace') }}"
                autocomplete="off"
            >
            <p
                x-show="normalizedQuery"
                x-cloak
                class="mt-1 text-[11px] text-slate-500"
                x-text="visibleCount === 1 ? '{{ __('1 feature matches') }}' : `{{ __(':count features match') }}`.replace(':count', visibleCount)`"
            ></p>
        </div>

        @foreach ($workspace['groups'] as $group)
            <section class="mb-8" x-show="groupVisible(@js($group['label']))" x-cloak>
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group['label'] }}</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($group['items'] as $item)
                        <div x-show="cardVisible(@js($item['id']))" x-cloak>
                            @include('admin.settings.partials.settings-tile', [
                                'title' => $item['label'],
                                'description' => $item['description'],
                                'icon' => $item['icon'],
                                'href' => $item['href'],
                                'comingSoon' => $item['comingSoon'],
                                'statusLabel' => $item['statusLabel'],
                                'statusVariant' => $item['statusVariant'],
                            ])
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <p
            x-show="visibleCount === 0"
            x-cloak
            class="rounded-lg border border-dashed border-erp-border px-4 py-8 text-center text-sm text-slate-500"
        >
            {{ __('No features match your search.') }}
        </p>
    </div>
</x-admin-layout>
