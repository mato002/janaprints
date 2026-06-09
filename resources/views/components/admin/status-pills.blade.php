@props([
    'options' => [],
    'param' => 'status',
    'current' => null,
    'turboFrame' => 'erp-main',
])

@php
    $current = $current ?? request($param, '');
@endphp

@if (count($options) > 1)
    <div {{ $attributes->merge(['class' => 'erp-table-chips flex flex-wrap gap-1.5']) }} role="tablist" aria-label="{{ __('Filter by :param', ['param' => str_replace('_', ' ', $param)]) }}">
        @foreach ($options as $option)
            @php
                $value = $option['value'] ?? $option['id'] ?? '';
                $label = $option['label'] ?? ($value === '' || $value === 'all' ? __('All') : $value);
                $query = request()->query();
                if ($value === '' || $value === 'all') {
                    unset($query[$param]);
                } else {
                    $query[$param] = $value;
                }
                unset($query['page']);
                $url = url()->current().($query !== [] ? '?'.http_build_query($query) : '');
                $isActive = ($value === '' || $value === 'all')
                    ? ($current === '' || $current === null || $current === 'all')
                    : (string) $current === (string) $value;
            @endphp
            <a
                href="{{ $url }}"
                @class([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $isActive,
                ])
                @if ($turboFrame) data-turbo-frame="{{ $turboFrame }}" @endif
                role="tab"
                @if ($isActive) aria-selected="true" @endif
            >{{ $label }}</a>
        @endforeach
    </div>
@endif
