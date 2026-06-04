<section class="mb-6" aria-label="{{ __('Quality intelligence') }}">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Intelligence') }}</h2>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card>
            <h3 class="text-sm font-semibold text-red-800">{{ __('Recent Failures') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Latest failed inspections') }}</p>
            <ul class="mt-3 divide-y divide-erp-border text-sm">
                @forelse ($widgets['recent_failures'] as $row)
                    <li class="py-2">
                        @if ($row['job_id'] && Route::has('admin.production.job-cards.show'))
                            <a href="{{ route('admin.production.job-cards.show', $row['job_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_number'] }}</a>
                        @else
                            <span class="font-mono">{{ $row['job_number'] }}</span>
                        @endif
                        <span class="block text-slate-500">{{ $row['customer'] }} · {{ $row['inspector'] }}</span>
                        <span class="text-xs text-red-700">{{ $row['checked_at'] }}</span>
                        @if (! empty($row['comments']))
                            <span class="mt-0.5 block truncate text-xs text-slate-500" title="{{ $row['comments'] }}">{{ $row['comments'] }}</span>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-slate-500">{{ __('No failed inspections recorded.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-semibold text-amber-800">{{ __('Recent Holds') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Jobs currently on hold') }}</p>
            <ul class="mt-3 divide-y divide-erp-border text-sm">
                @forelse ($widgets['recent_holds'] as $row)
                    <li class="py-2">
                        @if (Route::has('admin.production.job-cards.show'))
                            <a href="{{ route('admin.production.job-cards.show', $row['job_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_number'] }}</a>
                        @else
                            <span class="font-mono">{{ $row['job_number'] }}</span>
                        @endif
                        <span class="block text-slate-500">{{ $row['customer'] }}</span>
                        <span class="text-xs text-amber-800">
                            {{ __('Held since :date', ['date' => $row['held_since']]) }}
                            @if ($row['inspector'] !== '—')
                                · {{ $row['inspector'] }}
                            @endif
                        </span>
                        @if (! empty($row['comments']))
                            <span class="mt-0.5 block truncate text-xs text-slate-500" title="{{ $row['comments'] }}">{{ $row['comments'] }}</span>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-slate-500">{{ __('No jobs on hold.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-semibold text-erp-primary">{{ __('Jobs Requiring Rework') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Jobs in rework status') }}</p>
            <ul class="mt-3 divide-y divide-erp-border text-sm">
                @forelse ($widgets['jobs_requiring_rework'] as $row)
                    <li class="py-2">
                        @if (Route::has('admin.production.job-cards.show'))
                            <a href="{{ route('admin.production.job-cards.show', $row['job_id']) }}" class="font-mono text-erp-accent hover:underline">{{ $row['job_number'] }}</a>
                        @else
                            <span class="font-mono">{{ $row['job_number'] }}</span>
                        @endif
                        <span class="block text-slate-500">{{ $row['customer'] }}</span>
                        <span class="text-xs text-slate-600">
                            {{ __('Flagged :date', ['date' => $row['flagged_at']]) }}
                            @if ($row['inspector'] !== '—')
                                · {{ $row['inspector'] }}
                            @endif
                        </span>
                        @if (! empty($row['comments']))
                            <span class="mt-0.5 block truncate text-xs text-slate-500" title="{{ $row['comments'] }}">{{ $row['comments'] }}</span>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-slate-500">{{ __('No jobs in rework.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</section>
