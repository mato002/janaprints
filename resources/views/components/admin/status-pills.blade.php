@props([
    'options' => [],
    'param' => 'status',
    'current' => null,
    'turboFrame' => 'erp-main',
    'formMode' => true,
])

@php
    $current = $current ?? request($param, '');

    $normalizeValue = static function ($value): string {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    };

    $isAllValue = static function ($value): bool {
        return $value === '' || $value === 'all' || $value === null;
    };

    $currentValue = $normalizeValue($current);
@endphp

@if (count($options) > 1)
    <div {{ $attributes->merge(['class' => 'erp-table-chips flex flex-wrap gap-1.5']) }} role="tablist" aria-label="{{ __('Filter by :param', ['param' => str_replace('_', ' ', $param)]) }}">
        @if ($formMode)
            <input type="hidden" name="{{ $param }}" value="{{ $currentValue }}">

            @foreach ($options as $option)
                @php
                    $value = $option['value'] ?? $option['id'] ?? '';
                    $label = $option['label'] ?? ($isAllValue($value) ? __('All') : $value);
                    $storedValue = $normalizeValue($value);
                    $isActive = $isAllValue($value)
                        ? ($currentValue === '' || $currentValue === 'all')
                        : $currentValue === (string) $value;
                @endphp
                <button
                    type="button"
                    data-erp-filter-pill
                    data-erp-filter-param="{{ $param }}"
                    data-erp-filter-value="{{ $storedValue }}"
                    @class([
                        'erp-filter-pill',
                        'erp-filter-pill--active' => $isActive,
                    ])
                    role="tab"
                    @if ($isActive) aria-selected="true" @endif
                >{{ $label }}</button>
            @endforeach
        @else
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
                    $isActive = $isAllValue($value)
                        ? ($currentValue === '' || $currentValue === 'all')
                        : $currentValue === (string) $value;
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
        @endif
    </div>
@endif
