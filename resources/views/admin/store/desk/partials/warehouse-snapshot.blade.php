@if (count($warehouseSnapshot ?? []) > 0)
    <x-admin.card class="mb-4">
        <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Warehouse snapshot') }}</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($warehouseSnapshot as $warehouse)
                <a href="{{ $warehouse['url'] }}" class="block rounded-lg border border-erp-border bg-white p-3 transition hover:border-erp-accent/40 hover:bg-slate-50" data-turbo-frame="erp-main">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="font-medium text-slate-900">{{ $warehouse['name'] }}</span>
                        <span class="text-xs font-semibold tabular-nums text-erp-primary">{{ $warehouse['fill_percent'] }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full bg-erp-accent transition-all"
                            style="width: {{ min(100, max(0, $warehouse['fill_percent'])) }}%"
                        ></div>
                    </div>
                    <p class="mt-1 text-[10px] uppercase tracking-wide text-slate-500">{{ __('Stores') }}</p>
                </a>
            @endforeach
        </div>
    </x-admin.card>
@endif
