@props([
    'name' => 'branch_id',
    'branches',
    'selected' => null,
    'selectClass' => 'erp-input mt-1 min-w-[10rem]',
    'labelClass' => 'text-[11px] text-slate-500',
    'showLabel' => true,
])

@php
    $canViewConsolidated = app(\App\Support\Security\ConsolidatedViewGovernance::class)->canViewConsolidated(auth()->user());
@endphp

<div>
    @if ($showLabel)
        <label class="{{ $labelClass }}" for="{{ $name }}">{{ __('Branch') }}</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => $selectClass]) }}>
        @if ($canViewConsolidated)
            <option value="" @selected($selected === null || $selected === '')>{{ __('All branches') }}</option>
        @endif
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) ($selected ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
        @endforeach
    </select>
</div>
