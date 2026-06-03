@php
    $display = $empty ?? '—';
@endphp

@if ($value === null || $value === '')
    <span class="text-slate-400">{{ $display }}</span>
@elseif ($type === 'boolean')
    {{ filter_var($value, FILTER_VALIDATE_BOOLEAN) ? __('Yes') : __('No') }}
@else
    {{ $value }}
@endif
