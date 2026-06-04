<x-admin.card>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Workload by Work Center') }}</h2>
    <div class="space-y-4">
        @forelse ($dashboard['workload'] as $center)
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    @if ($center['url'] ?? null)
                        <a href="{{ $center['url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $center['name'] }}</a>
                    @else
                        <span class="font-medium text-erp-primary">{{ $center['name'] }}</span>
                    @endif
                    <div class="flex flex-wrap gap-3 text-xs text-slate-500 tabular-nums">
                        <span>{{ __('Active') }}: {{ $center['active_jobs'] }}</span>
                        <span>{{ __('Queued') }}: {{ $center['queue_count'] }}</span>
                        @if ($center['awaiting_qc'] > 0)
                            <span class="text-amber-700">{{ __('Awaiting QC') }}: {{ $center['awaiting_qc'] }}</span>
                        @endif
                        @if ($center['delayed_jobs'] > 0)
                            <span class="text-red-700">{{ __('Delayed') }}: {{ $center['delayed_jobs'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="{{ ($center['is_overbooked'] ?? false) ? 'bg-red-500' : (($center['utilization_percent'] ?? 0) >= 80 ? 'bg-amber-500' : 'bg-erp-accent') }} h-full rounded-full transition-all" style="width: {{ min(100, $center['bar_percent'] ?? 0) }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">
                    {{ __('Utilization') }}: {{ $center['utilization_percent'] }}%
                    · {{ __('Capacity') }}: {{ $center['capacity'] }}
                </p>
            </div>
        @empty
            <x-admin.empty-state :title="__('No work centers')" :description="__('Configure work centers to see workload.')" />
        @endforelse
    </div>
</x-admin.card>
