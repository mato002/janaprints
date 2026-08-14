@props([
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'status' => null,
    'statusLabel' => null,
    'statusDetail' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
])

@if ($comingSoon || empty($href))
    <div
        {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-dashed border-erp-border bg-erp-card/60 p-3 sm:p-5 opacity-75']) }}
        aria-disabled="true"
    >
        @include('admin.settings.partials.control-center-card-inner')
    </div>
@else
    <a
        href="{{ $href }}"
        data-turbo-action="advance"
        {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-erp-border bg-erp-card p-3 sm:p-5 shadow-card transition-all duration-200 hover:border-erp-accent/40 hover:shadow-card-hover focus:outline-none focus:ring-2 focus:ring-erp-accent focus:ring-offset-2']) }}
    >
        @include('admin.settings.partials.control-center-card-inner')
    </a>
@endif
