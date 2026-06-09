@props([
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'exportRoute' => null,
    'exportQuery' => null,
    'exportRouteParams' => [],
    'formatInPath' => false,
    'postAction' => null,
    'postFields' => [],
    'canExport' => true,
    'disabledTitle' => null,
])

@php
    $postFields = collect($postFields)->filter(fn ($value) => $value !== null && $value !== '')->all();
    $formatLabels = [
        'csv' => __('Export CSV'),
        'excel' => __('Export Excel'),
        'pdf' => __('Export PDF'),
    ];
    $formats = ['csv', 'excel', 'pdf'];
    $query = $exportQuery ?? request()->query();
    $routeParams = $exportRouteParams ?? [];
    $availableFormats = [];

    if ($canExport && filled($exportRoute)) {
        foreach ($formats as $format) {
            $availableFormats[$format] = [
                'type' => 'url',
                'url' => route($exportRoute, array_merge($routeParams, $query, ['format' => $format])),
            ];
        }
    } else {
        foreach (['csv' => $csvUrl, 'excel' => $excelUrl, 'pdf' => $pdfUrl] as $format => $url) {
            if (filled($url)) {
                $availableFormats[$format] = ['type' => 'url', 'url' => $url];
            }
        }

        if ($postAction) {
            foreach ($formats as $format) {
                $availableFormats[$format] = ['type' => 'post', 'action' => $postAction];
            }
        }
    }

    $disabledTitle ??= __('You do not have permission to export');
@endphp

@if (! $canExport)
    <button
        type="button"
        class="erp-btn-secondary py-2 text-sm opacity-60"
        disabled
        title="{{ $disabledTitle }}"
    >
        <x-admin.icon name="download" class="h-4 w-4" />
        {{ __('Export') }}
    </button>
@elseif ($availableFormats === [])
    {{ $slot ?? '' }}
@else
    <div class="relative" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">
        <button type="button" class="erp-btn-secondary py-2 text-sm" @click.stop="exportOpen = !exportOpen">
            <x-admin.icon name="download" class="h-4 w-4" />
            {{ __('Export') }}
        </button>
        <div
            x-show="exportOpen"
            x-cloak
            class="absolute end-0 z-20 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
        >
            @foreach ($formats as $format)
                @continue(! isset($availableFormats[$format]))
                @php $config = $availableFormats[$format]; @endphp
                @if ($config['type'] === 'url')
                    <a
                        href="{{ $config['url'] }}"
                        data-turbo="false"
                        data-turbo-frame="_top"
                        target="_top"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                        @click="exportOpen = false"
                    >{{ $formatLabels[$format] }}</a>
                @else
                    <form method="POST" action="{{ $config['action'] }}" class="contents" data-turbo="false" target="_top">
                        @csrf
                        <input type="hidden" name="format" value="{{ $format }}">
                        @foreach ($postFields as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button
                            type="submit"
                            class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                            @click="exportOpen = false"
                        >{{ $formatLabels[$format] }}</button>
                    </form>
                @endif
            @endforeach
            {{ $slot ?? '' }}
        </div>
    </div>
@endif
