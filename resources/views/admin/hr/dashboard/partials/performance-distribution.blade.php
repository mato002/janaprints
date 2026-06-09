@props(['distribution'])

<x-admin.card :title="__('Performance Distribution')">
    @if (! empty($distribution))
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($distribution as $item)
                <div class="rounded-lg border border-erp-border/70 p-3 text-center">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-1 text-xl font-bold tabular-nums text-erp-primary">{{ $item['count'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">{{ __('No performance reviews in the current period.') }}</p>
    @endif
</x-admin.card>
