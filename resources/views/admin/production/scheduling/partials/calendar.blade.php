@php
    $queryExceptMonth = array_merge(request()->except('page', 'month'), ['view' => 'calendar']);
@endphp

<x-admin.card>
    <div class="mb-4 flex items-center justify-between gap-3">
        <a
            href="{{ route('admin.production.scheduling.index', array_merge($queryExceptMonth, ['month' => $calendar['prev_month']])) }}"
            class="erp-btn-secondary text-sm"
        >
            {{ __('Previous') }}
        </a>
        <h3 class="text-sm font-semibold text-erp-primary">{{ $calendar['label'] }}</h3>
        <a
            href="{{ route('admin.production.scheduling.index', array_merge($queryExceptMonth, ['month' => $calendar['next_month']])) }}"
            class="erp-btn-secondary text-sm"
        >
            {{ __('Next') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[42rem] border-collapse text-sm">
            <thead>
                <tr class="text-xs uppercase tracking-wide text-slate-500">
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                        <th class="border border-erp-border bg-erp-page px-2 py-2 text-center font-medium">{{ __($weekday) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($calendar['weeks'] as $week)
                    <tr>
                        @foreach ($week as $day)
                            <td class="align-top border border-erp-border p-1 min-h-[5rem] w-[14.28%] {{ $day['in_month'] ? 'bg-white' : 'bg-erp-page/60' }} {{ $day['is_today'] ? 'ring-2 ring-inset ring-erp-accent/40' : '' }}">
                                <div class="mb-1 text-xs font-medium tabular-nums {{ $day['in_month'] ? 'text-slate-700' : 'text-slate-400' }}">
                                    {{ $day['label'] }}
                                </div>
                                <ul class="space-y-0.5">
                                    @foreach ($day['jobs'] as $job)
                                        <li>
                                            @if (Route::has('admin.production.job-cards.show'))
                                                <a
                                                    href="{{ $job['url'] ?? route('admin.production.job-cards.show', $job['public_id']) }}"
                                                    class="block truncate rounded px-1 py-0.5 text-[10px] leading-tight {{ $job['span'] === 'start' ? 'bg-erp-accent/15 text-erp-primary font-medium' : ($job['span'] === 'end' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700') }}"
                                                    title="{{ $job['job_number'] }} — {{ $job['customer'] }}"
                                                >
                                                    {{ $job['job_number'] }}
                                                </a>
                                            @else
                                                <span
                                                    class="block truncate rounded px-1 py-0.5 text-[10px] leading-tight bg-slate-100 text-slate-700"
                                                    title="{{ $job['job_number'] }} — {{ $job['customer'] }}"
                                                >
                                                    {{ $job['job_number'] }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
