@props([
    'action',
    'resetUrl',
    'filters' => [],
    'periods' => [],
    'showZeroCheckbox' => false,
    'full' => false,
    'hidden' => [],
    'periodLabel' => null,
    'customPeriodLabel' => null,
])

@php
    $periodLabel ??= __('Period');
    $customPeriodLabel ??= __('Custom range');
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$action" :reset-url="$resetUrl">
        @foreach ($hidden as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <select name="period_id" class="erp-toolbar-select" aria-label="{{ $periodLabel }}">
            <option value="">{{ $customPeriodLabel }}</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>
                    {{ $period->code }}@if (! empty($period->name)) — {{ $period->name }}@endif
                </option>
            @endforeach
        </select>
        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        @if ($showZeroCheckbox)
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                <input type="hidden" name="include_zero" value="0">
                <input type="checkbox" name="include_zero" value="1" @checked($full)>
                {{ __('Zero balances') }}
            </label>
        @endif
        {{ $slot ?? '' }}
    </x-admin.index-toolbar>
</x-admin.card>
