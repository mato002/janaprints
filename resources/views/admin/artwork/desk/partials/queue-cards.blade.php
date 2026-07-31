@php
    $statusColors = [
        'requested' => 'bg-slate-100 text-slate-700',
        'in_design' => 'bg-blue-100 text-blue-700',
        'submitted' => 'bg-indigo-100 text-indigo-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'revision_requested' => 'bg-amber-100 text-amber-800',
        'rejected' => 'bg-rose-100 text-rose-700',
    ];
    $availableRows = $availableRows ?? [];
    $myRows = $rows ?? [];
@endphp

<section class="designer-desk-queue space-y-3" aria-label="{{ __("Today's queue") }}">
    @if (count($availableRows) > 0)
        <div class="rounded-xl border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-2 border-b border-amber-100 bg-amber-50/60 px-3 py-2.5">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Available to claim') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Claim first — only one designer can work a job.') }}</p>
                </div>
                <span class="text-[11px] font-semibold tabular-nums text-amber-800">{{ count($availableRows) }}</span>
            </div>
            <ul class="max-h-[28vh] divide-y divide-slate-100 overflow-y-auto">
                @foreach ($availableRows as $row)
                    <li
                        x-show="rowVisible($el)"
                        data-filter-available="1"
                        data-filter-mine="0"
                        data-urgency-due-today="{{ $row['is_due_today'] ? '1' : '0' }}"
                        data-urgency-overdue="{{ $row['is_late'] ? '1' : '0' }}"
                        data-filter-working="0"
                        data-filter-review="0"
                        data-filter-late="{{ $row['is_late'] ? '1' : '0' }}"
                        data-filter-high="{{ ($row['is_high'] ?? false) ? '1' : '0' }}"
                        data-filter-today="{{ ($row['is_due_today'] || $row['is_late']) ? '1' : '0' }}"
                    >
                        <button
                            type="button"
                            class="designer-desk-queue-card w-full px-3 py-3 text-left transition hover:bg-amber-50/40"
                            :class="{ 'bg-erp-accent/5 ring-1 ring-inset ring-erp-accent/30': selectedKey === @js($row['key']) }"
                            @click="selectRequest(@js($row['key']))"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $row['title'] }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $row['customer'] ?? '—' }} · <span class="font-mono">{{ $row['request_number'] }}</span></p>
                                </div>
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-900">{{ __('Unclaimed') }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span @class(['font-semibold', 'text-rose-700' => $row['is_late'], 'text-amber-700' => $row['is_due_today'] && ! $row['is_late']])>{{ __('Due') }} {{ $row['due_date'] }}</span>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-erp-border bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2.5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('My queue') }}</h2>
                <p class="text-[11px] text-slate-500">{{ __('Jobs claimed by you — upload PDF, then mark complete.') }}</p>
            </div>
            <span class="text-[11px] font-semibold tabular-nums text-slate-500">{{ count($myRows) }}</span>
        </div>

        @if (count($myRows) === 0)
            <div class="px-4 py-8 text-center">
                @if (count($availableRows) > 0)
                    <p class="text-sm font-semibold text-slate-800">{{ __('Nothing claimed yet') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Pick a job from Available to claim and start working.') }}</p>
                @else
                    <p class="text-sm font-semibold text-slate-800">{{ __('You\'re all caught up') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('No jobs assigned. The next artwork request will appear automatically.') }}</p>
                @endif
            </div>
        @else
            <ul class="max-h-[42vh] divide-y divide-slate-100 overflow-y-auto">
                @foreach ($myRows as $row)
                    <li
                        x-show="rowVisible($el)"
                        data-filter-available="0"
                        data-filter-mine="1"
                        data-urgency-due-today="{{ $row['is_due_today'] ? '1' : '0' }}"
                        data-urgency-overdue="{{ $row['is_late'] ? '1' : '0' }}"
                        data-urgency-waiting="{{ $row['is_waiting'] ? '1' : '0' }}"
                        data-urgency-new="{{ $row['status'] === 'requested' ? '1' : '0' }}"
                        data-filter-working="{{ ($row['is_working'] ?? false) ? '1' : '0' }}"
                        data-filter-review="{{ ($row['is_review'] ?? false) ? '1' : '0' }}"
                        data-filter-late="{{ $row['is_late'] ? '1' : '0' }}"
                        data-filter-high="{{ ($row['is_high'] ?? false) ? '1' : '0' }}"
                        data-filter-today="{{ ($row['is_due_today'] || $row['is_late']) ? '1' : '0' }}"
                    >
                        <button
                            type="button"
                            class="designer-desk-queue-card w-full px-3 py-3 text-left transition hover:bg-slate-50"
                            :class="{ 'bg-erp-accent/5 ring-1 ring-inset ring-erp-accent/30': selectedKey === @js($row['key']) }"
                            @click="selectRequest(@js($row['key']))"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $row['title'] }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $row['customer'] ?? '—' }} · <span class="font-mono">{{ $row['request_number'] }}</span></p>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                    $statusColors[$row['status']] ?? 'bg-slate-100 text-slate-700',
                                ])>{{ $row['status_label'] }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                <span @class([
                                    'font-semibold',
                                    'text-rose-700' => $row['is_late'],
                                    'text-amber-700' => $row['is_due_today'] && ! $row['is_late'],
                                    'text-slate-500' => ! $row['is_late'] && ! $row['is_due_today'],
                                ])>{{ __('Due') }} {{ $row['due_date'] }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500">{{ $row['version_label'] ?? $row['version'] }}</span>
                                @if (! empty($row['designer']))
                                    <span class="text-slate-400">·</span>
                                    <span class="font-medium text-slate-600">{{ $row['designer'] }}</span>
                                @endif
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>

            @if ($requests->hasPages())
                <div class="border-t border-erp-border px-3 py-2">{{ $requests->links() }}</div>
            @endif
        @endif
    </div>
</section>
