@php
    $employee = $employee ?? null;
@endphp

<div class="md:col-span-2 mt-2 border-t border-erp-border pt-4">
    <h3 class="text-sm font-semibold text-erp-primary">{{ $title }}</h3>
    @if (! empty($description))
        <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
    @endif
</div>
