<section class="mb-6" aria-label="{{ __('Work center load') }}">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Work Center Load') }}</h2>
        <p class="text-xs text-slate-500">
            {{ __('Planning capacity: :count concurrent jobs per center', ['count' => $workCenterLoad['default_capacity']]) }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($workCenterLoad['centers'] as $center)
            @php
                $barWidth = min(100, $center['utilization_percent']);
                $barClass = $center['is_overbooked']
                    ? 'bg-red-500'
                    : ($center['utilization_percent'] >= 80 ? 'bg-amber-500' : 'bg-erp-accent');
            @endphp
            <x-admin.card class="{{ $center['is_overbooked'] ? 'ring-1 ring-red-200' : '' }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-erp-primary">{{ $center['name'] }}</h3>
                        <p class="text-xs text-slate-500">{{ $center['code'] }}</p>
                    </div>
                    @if ($center['is_overbooked'])
                        <span class="erp-badge shrink-0 bg-red-100 text-red-800">{{ __('Overbooked') }}</span>
                    @endif
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Assigned jobs') }}</dt>
                        <dd class="font-semibold tabular-nums text-erp-primary">{{ $center['assigned_jobs'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Capacity utilization') }}</dt>
                        <dd class="font-semibold tabular-nums text-erp-primary">{{ $center['utilization_percent'] }}%</dd>
                    </div>
                </dl>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="{{ $barClass }} h-full rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    {{ __(':assigned of :capacity planning slots', ['assigned' => $center['assigned_jobs'], 'capacity' => $center['capacity']]) }}
                </p>
            </x-admin.card>
        @empty
            <x-admin.card class="md:col-span-2 xl:col-span-3">
                <p class="text-sm text-slate-500">{{ __('No active work centers.') }}</p>
            </x-admin.card>
        @endforelse
    </div>

    @if ($workCenterLoad['overbooked_count'] > 0)
        <p class="mt-3 text-sm text-red-700">
            {{ trans_choice(':count work center is overbooked.|:count work centers are overbooked.', $workCenterLoad['overbooked_count'], ['count' => $workCenterLoad['overbooked_count']]) }}
        </p>
    @endif
</section>
