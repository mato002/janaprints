<section class="mb-6" aria-label="{{ __('Scheduling warnings') }}">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Warnings') }}</h2>
        @if ($warnings['counts']['total'] > 0)
            <span class="erp-badge bg-amber-100 text-amber-900">{{ $warnings['counts']['total'] }}</span>
        @endif
    </div>

    @if ($warnings['counts']['total'] === 0)
        <x-admin.card>
            <p class="text-sm text-slate-600">{{ __('No scheduling warnings. All tracked jobs have dates and no capacity overlaps detected.') }}</p>
        </x-admin.card>
    @else
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-admin.card>
                <h3 class="text-sm font-semibold text-red-800">{{ __('Late jobs') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Past planned end date') }}</p>
                <ul class="mt-3 divide-y divide-erp-border text-sm">
                    @forelse ($warnings['late_jobs'] as $row)
                        <li class="py-2">
                            @if (Route::has('admin.production.job-cards.show'))
                                <a href="{{ route('admin.production.job-cards.show', $row['job_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_number'] }}</a>
                            @else
                                <span class="font-mono">{{ $row['job_number'] }}</span>
                            @endif
                            <span class="block text-slate-500">{{ $row['customer'] }}</span>
                            <span class="text-xs text-red-700">
                                {{ __('Due :date', ['date' => $row['due_date']]) }}
                                · {{ trans_choice(':count day late|:count days late', $row['days_late'], ['count' => $row['days_late']]) }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('None') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-sm font-semibold text-amber-800">{{ __('Capacity conflicts') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Overlapping schedules on the same work center') }}</p>
                <ul class="mt-3 divide-y divide-erp-border text-sm">
                    @forelse ($warnings['capacity_conflicts'] as $row)
                        <li class="py-2">
                            <span class="font-medium text-erp-primary">{{ $row['work_center'] }}</span>
                            <span class="mt-1 block">
                                @if (Route::has('admin.production.job-cards.show'))
                                    <a href="{{ route('admin.production.job-cards.show', $row['job_a_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_a_number'] }}</a>
                                    ↔
                                    <a href="{{ route('admin.production.job-cards.show', $row['job_b_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_b_number'] }}</a>
                                @else
                                    <span class="font-mono">{{ $row['job_a_number'] }}</span>
                                    ↔
                                    <span class="font-mono">{{ $row['job_b_number'] }}</span>
                                @endif
                            </span>
                            <span class="text-xs text-slate-500">
                                {{ __('Overlap :start – :end', ['start' => \Carbon\Carbon::parse($row['overlap_start'])->format('M j'), 'end' => \Carbon\Carbon::parse($row['overlap_end'])->format('M j, Y')]) }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('None') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-sm font-semibold text-slate-800">{{ __('Missing schedule dates') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Incomplete planned start or end') }}</p>
                <ul class="mt-3 divide-y divide-erp-border text-sm">
                    @forelse ($warnings['missing_schedule_dates'] as $row)
                        <li class="py-2">
                            @if (Route::has('admin.production.job-cards.show'))
                                <a href="{{ route('admin.production.job-cards.show', $row['job_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_number'] }}</a>
                            @else
                                <span class="font-mono">{{ $row['job_number'] }}</span>
                            @endif
                            <span class="block text-slate-500">{{ $row['customer'] }}</span>
                            <span class="text-xs text-amber-800">{{ __('Missing :fields', ['fields' => $row['missing']]) }}</span>
                            @if ($row['has_queue'])
                                <span class="text-xs text-slate-500">{{ __('Queued at :centers', ['centers' => $row['work_centers'] !== [] ? implode(', ', $row['work_centers']) : __('work center')]) }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('None') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>
        </div>
    @endif
</section>
