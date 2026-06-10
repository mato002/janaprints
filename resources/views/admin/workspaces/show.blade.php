<x-admin-layout
    :title="$workspace['title']"
    :compact-workspace="true"
>
    <div
        x-data="workspaceHub(@js($cards))"
        x-cloak
        class="workspace-hub w-full min-w-0 space-y-3"
    >
        <x-admin.compact-workspace-header
            :title="$workspace['title']"
            :description="$workspace['description']"
        >
            <x-slot:search>
                <div class="relative w-full sm:w-56">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                    <input
                        type="search"
                        x-model.debounce.150ms="query"
                        @input="syncVisibleCards()"
                        class="erp-input w-full py-1.5 pl-8 text-sm"
                        placeholder="{{ __('Search in :workspace…', ['workspace' => $workspace['title']]) }}"
                        aria-label="{{ __('Search workspace') }}"
                        autocomplete="off"
                    >
                </div>
            </x-slot:search>
        </x-admin.compact-workspace-header>

        <p
            x-show="normalizedQuery"
            x-cloak
            class="text-[11px] text-slate-500"
            x-text="visibleCount === 1 ? '{{ __('1 feature matches') }}' : `{{ __(':count features match') }}`.replace(':count', visibleCount)`"
        ></p>

        @foreach ($workspace['groups'] as $group)
            <section class="mb-4" x-show="groupVisible(@js($group['label']))" x-cloak>
                <h2 class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $group['label'] }}</h2>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
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
            class="rounded-lg border border-dashed border-erp-border px-4 py-6 text-center text-sm text-slate-500"
        >
            {{ __('No features match your search.') }}
        </p>
    </div>
</x-admin-layout>
