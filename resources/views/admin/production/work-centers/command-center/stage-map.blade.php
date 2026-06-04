<x-admin.card>
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Stage Map') }}</h2>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:overflow-x-auto lg:pb-1">
        @forelse ($dashboard['stages'] as $index => $stage)
            <div class="flex min-w-[10rem] flex-1 flex-col">
                @if ($index > 0)
                    <span class="mb-2 hidden text-slate-300 lg:block lg:text-center" aria-hidden="true">→</span>
                @endif
                <div class="flex h-full flex-col rounded-xl border border-erp-border bg-white p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stage['code'] }}</p>
                            <p class="mt-1 font-semibold text-erp-primary">{{ $stage['name'] }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold tabular-nums text-erp-primary">{{ $stage['job_count'] }}</span>
                    </div>
                    <p class="mt-2 text-[10px] uppercase tracking-wide text-slate-400">{{ __('Jobs in stage') }}</p>
                    @if (! empty($stage['linked_work_centers']))
                        <div class="mt-3 border-t border-erp-border pt-3">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ __('Work centers') }}</p>
                            <ul class="mt-1 space-y-1 text-xs text-slate-600">
                                @foreach ($stage['linked_work_centers'] as $center)
                                    <li>{{ $center['name'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="mt-3 border-t border-erp-border pt-3 text-xs text-slate-400">{{ __('No linked work centers') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No production stages configured.') }}</p>
        @endforelse
    </div>
</x-admin.card>
