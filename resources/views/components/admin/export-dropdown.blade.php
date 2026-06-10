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
            $params = array_merge($routeParams, $query);

            if ($formatInPath) {
                $params['format'] = $format;
            } else {
                unset($params['format']);
                $params['format'] = $format;
            }

            $availableFormats[$format] = [
                'type' => 'url',
                'url' => route($exportRoute, $params),
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
    <div class="relative" x-data="erpExportDropdown()" @click.outside="exportOpen = false">
        <button
            type="button"
            class="erp-btn-secondary py-2 text-sm"
            :disabled="exporting"
            @click.stop="!exporting && (exportOpen = !exportOpen)"
        >
            <span x-show="!exporting" class="inline-flex items-center gap-2">
                <x-admin.icon name="download" class="h-4 w-4" />
                {{ __('Export') }}
            </span>
            <span x-show="exporting" x-cloak class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                {{ __('Exporting…') }}
            </span>
        </button>
        <div
            x-show="exportOpen && !exporting"
            x-cloak
            class="absolute end-0 z-20 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
        >
            @foreach ($formats as $format)
                @continue(! isset($availableFormats[$format]))
                @php $config = $availableFormats[$format]; @endphp
                @if ($config['type'] === 'url')
                    <button
                        type="button"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                        @click.prevent="downloadUrl(@js($config['url']), @js($formatLabels[$format]))"
                    >{{ $formatLabels[$format] }}</button>
                @else
                    <button
                        type="button"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                        @click.prevent="submitPost(@js($config['action']), @js(array_merge(['_token' => csrf_token(), 'format' => $format], $postFields)), @js($formatLabels[$format]))"
                    >{{ $formatLabels[$format] }}</button>
                @endif
            @endforeach
            {{ $slot ?? '' }}
        </div>
    </div>
@endif
