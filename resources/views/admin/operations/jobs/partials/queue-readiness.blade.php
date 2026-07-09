@php($readiness = $queueReadiness ?? [])
<x-admin.card class="mb-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Queue Readiness') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Connection') }}: <span class="font-medium text-erp-primary">{{ strtoupper($readiness['connection'] ?? 'unknown') }}</span>
                @if ($readiness['healthy'] ?? false)
                    · <span class="text-emerald-700">{{ __('Healthy') }}</span>
                @else
                    · <span class="text-amber-700">{{ __('Attention required') }}</span>
                @endif
            </p>
        </div>
        <p class="text-xs text-slate-500">{{ __('See docs/PRODUCTION_QUEUE_AND_SCHEDULER_RUNBOOK.md in the repository.') }}</p>
    </div>

    @if (! empty($readiness['warnings']))
        <ul class="mt-4 space-y-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            @foreach ($readiness['warnings'] as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Queue Backlog') }}</h3>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($readiness['backlog'] ?? [] as $queue => $count)
                    <div class="rounded-lg border border-erp-border bg-erp-page px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $queue }}</p>
                        <p @class(['text-lg font-semibold tabular-nums', 'text-red-600' => $count >= 100, 'text-erp-primary' => $count < 100])>{{ number_format($count) }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ __('Failed jobs') }}: <span class="font-semibold tabular-nums">{{ number_format($readiness['failed_jobs'] ?? 0) }}</span></p>
        </div>

        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Worker Commands') }}</h3>
            <div class="space-y-3">
                @foreach ($readiness['worker_commands'] ?? [] as $item)
                    <div class="rounded-lg border border-erp-border bg-erp-page px-3 py-2">
                        <p class="text-sm font-medium text-erp-primary">{{ $item['label'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $item['description'] }}</p>
                        <pre class="mt-2 overflow-x-auto rounded bg-slate-900 p-2 text-[11px] text-slate-100">{{ $item['command'] }}</pre>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Scheduler Checklist') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Command') }}</th>
                        <th class="px-3 py-2">{{ __('Schedule') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($readiness['scheduler_tasks'] ?? [] as $task)
                        <tr class="border-b border-erp-border/60">
                            <td class="px-3 py-2 font-mono text-xs">{{ $task['command'] }}</td>
                            <td class="px-3 py-2">{{ $task['schedule'] }}</td>
                            <td class="px-3 py-2">
                                @if ($task['configured'])
                                    <span class="text-emerald-700">{{ __('Configured') }}</span>
                                @else
                                    <span class="text-amber-700">{{ __('Recommended') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin.card>
