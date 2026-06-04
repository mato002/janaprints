<x-admin-layout
    :title="$workspace['title']"
>
    <x-admin.page-header
        :title="$workspace['title']"
        :description="$workspace['description']"
    />

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
                placeholder="{{ __('Search commercial workspaces…') }}"
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

        <section class="mb-8" x-show="groupVisible(@js(__('Workspaces')))" x-cloak>
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Workspaces') }}</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($workspace['items'] as $item)
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

        <p
            x-show="visibleCount === 0"
            x-cloak
            class="rounded-lg border border-dashed border-erp-border px-4 py-8 text-center text-sm text-slate-500"
        >
            {{ __('No workspaces match your search.') }}
        </p>
    </div>
</x-admin-layout>
