@props([
    'action',
    'resetUrl',
    'filters' => [],
    'periods' => [],
    'periodOptional' => true,
    'periodLabel' => null,
    'nonePeriodLabel' => null,
    'submitLabel' => null,
])

@php
    $periodLabel ??= __('Period cap (optional)');
    $nonePeriodLabel ??= __('None');
    $submitLabel ??= __('As of date');
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$action" :reset-url="$resetUrl">
        <input type="date" name="as_of_date" value="{{ $filters['as_of_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ $submitLabel }}" required>
        @if ($periodOptional && count($periods) > 0)
            <select name="period_id" class="erp-toolbar-select" aria-label="{{ $periodLabel }}">
                <option value="">{{ $nonePeriodLabel }}</option>
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                @endforeach
            </select>
        @endif
        {{ $slot ?? '' }}
    </x-admin.index-toolbar>
</x-admin.card>
