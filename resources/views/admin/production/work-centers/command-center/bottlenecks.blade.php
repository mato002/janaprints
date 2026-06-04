<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-admin.card>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Bottleneck Detection') }}</h2>

        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50/50 p-3">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-800">{{ __('Most congested work center') }}</p>
            @if ($dashboard['bottlenecks']['most_congested'] ?? null)
                <p class="mt-1 text-lg font-semibold text-erp-primary">{{ $dashboard['bottlenecks']['most_congested']['name'] }}</p>
                <p class="text-sm text-slate-600">{{ __(':count queue entries', ['count' => $dashboard['bottlenecks']['most_congested']['queue_count']]) }}</p>
            @else
                <p class="mt-1 text-sm text-slate-500">{{ __('No congestion detected.') }}</p>
            @endif
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Work centers with queued jobs') }}</h3>
                <ul class="mt-2 divide-y divide-erp-border text-sm">
                    @forelse ($dashboard['bottlenecks']['with_queued_jobs'] ?? [] as $center)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $center['name'] }}</span>
                            <span class="tabular-nums text-amber-700">{{ $center['queue_count'] }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('No queued backlog.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Work centers with delayed jobs') }}</h3>
                <ul class="mt-2 divide-y divide-erp-border text-sm">
                    @forelse ($dashboard['bottlenecks']['with_delayed_jobs'] ?? [] as $center)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $center['name'] }}</span>
                            <span class="tabular-nums text-red-700">{{ $center['delayed_jobs'] }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('No delayed jobs by work center.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Work centers with no activity') }}</h3>
                <ul class="mt-2 divide-y divide-erp-border text-sm">
                    @forelse ($dashboard['bottlenecks']['idle_centers'] ?? [] as $center)
                        <li class="py-2 text-slate-600">{{ $center['name'] }}</li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('All work centers have active or queued work.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </x-admin.card>
</div>
