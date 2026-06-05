@props([
    'searchable' => true,
    'searchPlaceholder' => null,
    'exportable' => true,
    'selectable' => false,
    'filterable' => true,
    'exportFilename' => 'export',
    'exportCsvUrl' => null,
    'exportPdfUrl' => null,
    'chips' => [],
    'tableId' => null,
])

@php
    $searchPlaceholder ??= __('Search…');
    $tableId ??= 'erp-table-'.Str::random(6);
    $chipPayload = collect($chips)->map(fn ($chip) => [
        'id' => $chip['id'] ?? $chip['label'] ?? 'all',
        'label' => $chip['label'] ?? $chip['id'] ?? 'All',
    ])->values()->all();

    if ($chipPayload === []) {
        $chipPayload = [['id' => 'all', 'label' => __('All')]];
    }

    $gridConfig = [
        'exportFilename' => $exportFilename,
        'chips' => $chipPayload,
        'selectable' => $selectable,
        'tableId' => $tableId,
    ];
@endphp

<div
    x-data="erpDataTable(@js($gridConfig))"
    {{ $attributes->merge(['class' => 'erp-data-grid w-full min-w-0']) }}
>
    @if (count($chipPayload) > 1)
        <div class="erp-table-chips mb-3 flex flex-wrap gap-1.5">
            @foreach ($chipPayload as $chip)
                <button
                    type="button"
                    class="erp-filter-pill"
                    :class="activeChip === @js($chip['id']) ? 'erp-filter-pill--active' : ''"
                    @click="setChip(@js($chip['id']))"
                >
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    @endif

    <x-admin.card :padding="false" class="min-w-0">
        @if ($searchable || $filterable || $exportable || $selectable || isset($toolbar) || isset($bulk) || isset($filters))
            <div class="erp-table-toolbar border-b border-erp-border bg-white px-4 py-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                        @if ($searchable)
                            <div class="relative min-w-[12rem] flex-1 max-w-md">
                                <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    type="search"
                                    x-model="query"
                                    placeholder="{{ $searchPlaceholder }}"
                                    class="erp-input w-full py-2 pl-9 text-sm"
                                    aria-label="{{ __('Search table') }}"
                                />
                            </div>
                        @endif

                        @if ($filterable)
                            <button
                                type="button"
                                class="erp-btn-secondary py-2 text-sm"
                                @click="filterOpen = !filterOpen"
                                :class="filterOpen ? 'border-erp-accent text-erp-accent' : ''"
                            >
                                <x-admin.icon name="filter" class="h-4 w-4" />
                                {{ __('Filters') }}
                            </button>
                        @endif

                        <label class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span>{{ __('Rows') }}</span>
                            <select class="erp-select py-1 text-xs" x-model.number="pageSize" @change="setPageSize(pageSize)">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </label>

                        {{ $toolbar ?? '' }}
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <div
                            x-show="selectable && selectedCount > 0"
                            x-cloak
                            class="flex flex-wrap items-center gap-2 rounded-lg border border-erp-accent/20 bg-erp-accent/5 px-2 py-1"
                        >
                            <span class="text-xs font-medium text-erp-primary" x-text="`${selectedCount} {{ __('selected') }}`"></span>
                            @isset($bulk)
                                {{ $bulk }}
                            @else
                                <button type="button" class="erp-btn-ghost py-1 text-xs" @click="exportSelected()">{{ __('Export selected') }}</button>
                            @endisset
                        </div>

                        <div class="flex items-center gap-1 rounded-lg border border-erp-border bg-white p-1">
                            <button type="button" class="erp-btn-ghost py-1 text-xs" @click="previousPage()" :disabled="currentPage <= 1" :class="currentPage <= 1 ? 'opacity-40' : ''">{{ __('Prev') }}</button>
                            <span class="px-2 text-xs font-medium text-slate-500" x-text="currentPage"></span>
                            <button type="button" class="erp-btn-ghost py-1 text-xs" @click="nextPage()">{{ __('Next') }}</button>
                        </div>

                        @if ($exportable)
                            <div class="relative" @click.outside="exportOpen = false">
                                <button type="button" class="erp-btn-secondary py-2 text-sm" @click.stop="exportOpen = !exportOpen">
                                    <x-admin.icon name="download" class="h-4 w-4" />
                                    {{ __('Export') }}
                                </button>
                                <div
                                    x-show="exportOpen"
                                    x-cloak
                                    class="absolute end-0 z-20 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
                                >
                                    @if ($exportCsvUrl)
                                        <a
                                            href="{{ $exportCsvUrl }}"
                                            data-turbo="false"
                                            class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                                            @click="exportOpen = false"
                                        >{{ __('Export CSV') }}</a>
                                    @else
                                        <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page" @click.stop="exportTable('csv')">{{ __('Export CSV') }}</button>
                                    @endif
                                    <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-400 cursor-not-allowed" disabled title="{{ __('Coming soon') }}">{{ __('Export Excel') }}</button>
                                    @if ($exportPdfUrl)
                                        <a
                                            href="{{ $exportPdfUrl }}"
                                            data-turbo="false"
                                            class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                                            @click="exportOpen = false"
                                        >{{ __('Export PDF') }}</a>
                                    @else
                                        <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-400 cursor-not-allowed" disabled title="{{ __('Coming soon') }}">{{ __('Export PDF') }}</button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{ $actions ?? '' }}
                    </div>
                </div>

                @if ($filterable && isset($filters))
                    <div x-show="filterOpen" x-cloak class="mt-3 rounded-lg border border-erp-border bg-erp-page/50 p-3">
                        <div class="flex flex-col gap-3">
                            {{ $filters }}
                            <div class="flex justify-end">
                                <button type="button" class="erp-btn-ghost py-1 text-xs" @click="resetFilters()">{{ __('Reset filters') }}</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="erp-table-scroll overflow-x-auto max-w-full">
            <table id="{{ $tableId }}" class="erp-table erp-table--grid">
                @isset($head)
                    <thead>{{ $head }}</thead>
                @endisset
                @isset($body)
                    <tbody>{{ $body }}</tbody>
                @else
                    <tbody>{{ $slot }}</tbody>
                @endisset
            </table>
            <div x-show="showNoResults" x-cloak class="border-t border-erp-border bg-white px-4 py-8 text-center text-sm text-slate-500">
                {{ __('No rows match your search or filters.') }}
            </div>
        </div>

        @isset($footer)
            <div class="border-t border-erp-border bg-white">
                {{ $footer }}
            </div>
        @endisset
    </x-admin.card>
</div>
