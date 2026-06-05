@props([
    'card',
])

@php
    $isInteractive = ! ($card['comingSoon'] ?? false) && filled($card['href'] ?? null);
    $shellClasses = 'forms-control-card group relative flex h-full min-h-[11.5rem] flex-col overflow-hidden rounded-xl border bg-white p-4 shadow-sm transition-colors';
    $enabledClasses = 'border-erp-border hover:border-erp-accent/35 hover:shadow-card-hover focus:outline-none focus:ring-2 focus:ring-erp-accent focus:ring-offset-2';
    $disabledClasses = 'border-dashed border-erp-border/80 bg-erp-page/40 opacity-85';
@endphp

@if ($isInteractive)
    <a
        href="{{ $card['href'] }}"
        data-turbo-action="advance"
        {{ $attributes->merge(['class' => "{$shellClasses} {$enabledClasses}"]) }}
    >
        @include('admin.settings.forms.partials.form-card-inner')
    </a>
@else
    <div
        {{ $attributes->merge(['class' => "{$shellClasses} {$disabledClasses}"]) }}
        aria-disabled="true"
    >
        @include('admin.settings.forms.partials.form-card-inner')
    </div>
@endif
