@props([
    'searchable' => true,
    'searchPlaceholder' => null,
    'exportable' => false,
])

@php
    $searchPlaceholder ??= __('Search…');
@endphp

<div x-data="tableSearch()" {{ $attributes->merge(['class' => 'w-full']) }}>
    <x-admin.card :padding="false">
        @if ($searchable || isset($toolbar) || $exportable || isset($bulk))
            <x-admin.filter-bar>
                @if ($searchable)
                    <div class="relative min-w-[12rem] flex-1 max-w-md">
                        <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 w-4 h-4 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            x-model="query"
                            placeholder="{{ $searchPlaceholder }}"
                            class="erp-input w-full pl-9 py-2"
                            aria-label="{{ __('Search table') }}"
                        />
                    </div>
                @endif
                {{ $toolbar ?? '' }}
                <x-slot name="actions">
                    @isset($bulk)
                        <div class="hidden sm:flex items-center gap-2">{{ $bulk }}</div>
                    @endisset
                    @if ($exportable)
                        <button type="button" class="erp-btn-secondary" title="{{ __('Export') }}">
                            {{ __('Export') }}
                        </button>
                    @endif
                    {{ $actions ?? '' }}
                </x-slot>
            </x-admin.filter-bar>
        @endif

        <div class="overflow-x-auto max-w-full">
            <table class="erp-table">
                @isset($head)
                    <thead>{{ $head }}</thead>
                @endisset
                @isset($body)
                    <tbody class="divide-y divide-erp-border bg-white">{{ $body }}</tbody>
                @else
                    <tbody class="divide-y divide-erp-border bg-white">{{ $slot }}</tbody>
                @endisset
            </table>
        </div>

        @isset($footer)
            <div class="border-t border-erp-border px-4 py-3">
                {{ $footer }}
            </div>
        @endisset
    </x-admin.card>
</div>
