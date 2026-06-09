@props([
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'count' => null,
    'statusLabel' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
    'domainLabel' => null,
    'compact' => true,
])

@php
    $statusClasses = match ($statusVariant ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $shellClasses = 'group relative flex h-full w-full min-w-0 min-h-[6.75rem] rounded-lg border bg-white p-2.5 transition-colors';
    $interactiveClasses = 'hover:border-erp-accent/30 hover:bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-erp-accent/25 focus:ring-offset-1';
    $disabledClasses = 'border-dashed border-erp-border/80 bg-erp-page/40 opacity-80';
    $enabledClasses = 'border-erp-border shadow-sm';
@endphp

@if ($comingSoon || empty($href))
    <div
        {{ $attributes->merge(['class' => "{$shellClasses} {$disabledClasses}"]) }}
        aria-disabled="true"
    >
        @include('admin.settings.partials.settings-tile-inner')
    </div>
@else
    <a
        href="{{ $href }}"
        data-turbo-action="advance"
        data-turbo-frame="erp-main"
        {{ $attributes->merge(['class' => "{$shellClasses} {$enabledClasses} {$interactiveClasses}"]) }}
    >
        @include('admin.settings.partials.settings-tile-inner')
    </a>
@endif
