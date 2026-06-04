<x-admin.card>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Workload') }}</h2>
    @forelse ($workload as $center)
        <div class="mb-3 last:mb-0">
            <div class="flex items-center justify-between gap-2 text-sm">
                @if ($center['url'] ?? null)
                    <a href="{{ $center['url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $center['name'] }}</a>
                @else
                    <span class="font-medium text-erp-primary">{{ $center['name'] }}</span>
                @endif
                <span class="text-xs text-slate-500 tabular-nums">{{ $center['active_jobs'] }} / {{ $center['queue_count'] }}</span>
            </div>
            <p class="text-[10px] text-slate-500">{{ __('Active jobs') }} / {{ __('Queue count') }}</p>
            @if ($center['show_utilization'] ?? false)
                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-erp-accent" style="width: {{ min(100, $center['utilization_percent']) }}%"></div>
                </div>
            @endif
        </div>
    @empty
        <x-admin.empty-state :title="__('No work centers')" :description="__('Configure work centers to see workload.')" />
    @endforelse
</x-admin.card>
