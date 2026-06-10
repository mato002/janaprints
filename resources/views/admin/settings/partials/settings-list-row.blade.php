@props([
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'statusLabel' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
    'domainLabel' => null,
])

@php
    $statusClasses = match ($statusVariant ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    use App\Support\Navigation\WorkspaceEmbed;

    $rowClasses = 'group flex w-full min-w-0 items-center gap-3 rounded-md border border-erp-border bg-white px-3 py-2 transition-colors hover:border-erp-accent/40 hover:bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-erp-accent focus:ring-offset-1';
    $disabledClasses = 'border-dashed border-erp-border/80 bg-erp-page/40 opacity-80';
    $resolvedHref = filled($href) ? WorkspaceEmbed::url($href) : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

@if ($comingSoon || empty($href))
    <div {{ $attributes->merge(['class' => "{$rowClasses} {$disabledClasses}"]) }} aria-disabled="true">
        @include('admin.settings.partials.settings-list-row-inner')
    </div>
@else
    <a href="{{ $resolvedHref }}" data-turbo-frame="{{ $turboFrame }}" data-turbo-action="advance" {{ $attributes->merge(['class' => $rowClasses]) }}>
        @include('admin.settings.partials.settings-list-row-inner')
    </a>
@endif
