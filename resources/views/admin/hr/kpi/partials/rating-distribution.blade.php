@props(['distribution'])

@if (! empty($distribution))
    <x-admin.card>
        <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Performance Rating Distribution') }}</h3>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            @foreach ($distribution as $item)
                <div class="rounded-lg border border-erp-border/70 p-4 text-center">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $item['count'] }}</p>
                </div>
            @endforeach
        </div>
    </x-admin.card>
@endif
