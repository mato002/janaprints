@props([
    'report',
    /** @var 'standard'|'extended' Standard: debit, credit, balance. Extended: adds debit/credit balance columns. */
    'tableMode' => 'standard',
])

@php
    use App\Enums\GlAccountTypeCode;

    $sectionDefs = [
        'asset' => ['label' => __('Assets'), 'icon' => 'building', 'order' => 1],
        'liability' => ['label' => __('Liabilities'), 'icon' => 'currency-dollar', 'order' => 2],
        'equity' => ['label' => __('Equity'), 'icon' => 'chart-pie', 'order' => 3],
        'revenue' => ['label' => __('Revenue'), 'icon' => 'cash', 'order' => 4],
        'expense' => ['label' => __('Expenses'), 'icon' => 'document-text', 'order' => 5],
        'other' => ['label' => __('Other'), 'icon' => 'inbox', 'order' => 6],
    ];

    $resolveSection = static function (string $accountCode): string {
        $prefix = substr(ltrim($accountCode), 0, 1);

        return match ($prefix) {
            GlAccountTypeCode::Asset->codeRangePrefix() => 'asset',
            GlAccountTypeCode::Liability->codeRangePrefix() => 'liability',
            GlAccountTypeCode::Equity->codeRangePrefix() => 'equity',
            GlAccountTypeCode::Revenue->codeRangePrefix() => 'revenue',
            GlAccountTypeCode::CostOfSales->codeRangePrefix(),
            GlAccountTypeCode::Expense->codeRangePrefix() => 'expense',
            default => 'other',
        };
    };

    $rowNetBalance = static function (array $row): float {
        if (array_key_exists('balance', $row)) {
            return (float) $row['balance'];
        }

        return round((float) ($row['debit_balance'] ?? 0) - (float) ($row['credit_balance'] ?? 0), 2);
    };

    $sections = collect($sectionDefs)->mapWithKeys(fn ($def, $key) => [
        $key => [
            ...$def,
            'key' => $key,
            'rows' => [],
            'count' => 0,
            'period_debit' => 0.0,
            'period_credit' => 0.0,
            'net_balance' => 0.0,
        ],
    ])->all();

    foreach ($report['rows'] as $row) {
        $key = $resolveSection($row['account_code']);
        $net = $rowNetBalance($row);
        $sections[$key]['rows'][] = $row;
        $sections[$key]['count']++;
        $sections[$key]['period_debit'] += (float) $row['period_debit'];
        $sections[$key]['period_credit'] += (float) $row['period_credit'];
        $sections[$key]['net_balance'] += $net;
    }

    foreach ($sections as $key => $section) {
        $sections[$key]['period_debit'] = round($section['period_debit'], 2);
        $sections[$key]['period_credit'] = round($section['period_credit'], 2);
        $sections[$key]['net_balance'] = round($section['net_balance'], 2);
        $sections[$key]['rows'] = collect($section['rows'])->sortBy('account_code')->values()->all();
    }

    $orderedSections = collect($sections)
        ->filter(fn ($s) => $s['count'] > 0 || $s['key'] !== 'other')
        ->sortBy('order')
        ->values();

    $rows = collect($report['rows']);
    $hasRows = $rows->isNotEmpty();

    $detailedRows = $rows->sortBy(function (array $row) use ($resolveSection, $sectionDefs) {
        $key = $resolveSection($row['account_code']);
        $order = $sectionDefs[$key]['order'] ?? 99;

        return sprintf('%02d-%s', $order, $row['account_code']);
    })->values();
@endphp

<div
    x-data="{
        view: 'summary',
        compact: false,
        open: {
            asset: false,
            liability: false,
            equity: false,
            revenue: false,
            expense: false,
            other: false,
        },
        toggleSection(key) { this.open[key] = !this.open[key]; },
    }"
    class="space-y-4"
>
    {{-- Sticky summary bar --}}
    <div class="sticky top-0 z-20 -mx-1 rounded-xl border border-erp-border bg-white/95 px-3 py-3 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/80">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Trial balance') }}</span>
                @if ($report['is_balanced'])
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">
                        <x-admin.icon name="shield-check" class="h-3.5 w-3.5" />
                        {{ __('Balanced') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-amber-600/20">
                        <x-admin.icon name="bell" class="h-3.5 w-3.5" />
                        {{ __('Out of balance') }}
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm tabular-nums">
                <div>
                    <span class="text-[11px] uppercase text-slate-400">{{ __('Total debits') }}</span>
                    <p class="font-semibold text-erp-primary">{{ number_format($report['total_debit'], 2) }}</p>
                </div>
                <div class="hidden h-8 w-px bg-erp-border sm:block" aria-hidden="true"></div>
                <div>
                    <span class="text-[11px] uppercase text-slate-400">{{ __('Total credits') }}</span>
                    <p class="font-semibold text-erp-primary">{{ number_format($report['total_credit'], 2) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1 rounded-lg border border-erp-border bg-slate-50/80 p-1" role="tablist">
                <button
                    type="button"
                    role="tab"
                    @click="view = 'summary'"
                    :class="view === 'summary' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                >{{ __('Summary') }}</button>
                <button
                    type="button"
                    role="tab"
                    @click="view = 'grouped'"
                    :class="view === 'grouped' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                >{{ __('Grouped') }}</button>
                <button
                    type="button"
                    role="tab"
                    @click="view = 'detailed'"
                    :class="view === 'detailed' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                >{{ __('Detailed') }}</button>
            </div>
        </div>
    </div>

    @if (! $hasRows)
        <x-admin.card>
            <p class="py-8 text-center text-sm text-slate-500">{{ __('No accounts with activity for the selected filters.') }}</p>
        </x-admin.card>
    @else
        {{-- Summary view --}}
        <div x-show="view === 'summary'" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($orderedSections as $section)
                    @if ($section['count'] === 0)
                        @continue
                    @endif
                    <x-admin.card :hover="true" class="border-l-4 border-l-erp-accent/60">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $section['label'] }}</p>
                                <p class="mt-2 text-lg font-semibold tabular-nums text-erp-primary">
                                    {{ number_format($section['net_balance'], 2) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $section['count'] }} {{ $section['count'] === 1 ? __('account') : __('accounts') }}
                                    · {{ __('Net balance') }}
                                </p>
                            </div>
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent">
                                <x-admin.icon :name="$section['icon']" class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="mt-3 flex gap-4 border-t border-erp-border/60 pt-3 text-[11px] tabular-nums text-slate-500">
                            <span>{{ __('Dr') }} {{ number_format($section['period_debit'], 2) }}</span>
                            <span>{{ __('Cr') }} {{ number_format($section['period_credit'], 2) }}</span>
                        </div>
                    </x-admin.card>
                @endforeach
            </div>

            <x-admin.card class="bg-slate-50/50">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-erp-primary">{{ __('Report totals') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Period debits and credits across all accounts') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-6 text-sm tabular-nums">
                        <div><span class="text-slate-400">{{ __('Debits') }}</span> <span class="font-semibold">{{ number_format($report['total_debit'], 2) }}</span></div>
                        <div><span class="text-slate-400">{{ __('Credits') }}</span> <span class="font-semibold">{{ number_format($report['total_credit'], 2) }}</span></div>
                        <div>
                            <span class="text-slate-400">{{ __('Status') }}</span>
                            <span class="font-semibold {{ $report['is_balanced'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $report['is_balanced'] ? __('Balanced') : __('Out of balance') }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- Grouped view --}}
        <div x-show="view === 'grouped'" x-cloak class="space-y-2">
            @foreach ($orderedSections as $section)
                @if ($section['count'] === 0)
                    @continue
                @endif
                <div class="overflow-hidden rounded-xl border border-erp-border bg-white">
                    <button
                        type="button"
                        @click="toggleSection('{{ $section['key'] }}')"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50/80"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent"
                            >
                                <x-admin.icon :name="$section['icon']" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium text-erp-primary">{{ $section['label'] }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $section['count'] }} {{ $section['count'] === 1 ? __('account') : __('accounts') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 text-sm tabular-nums">
                            <span class="hidden text-slate-500 sm:inline">{{ __('Net') }} <strong class="text-erp-primary">{{ number_format($section['net_balance'], 2) }}</strong></span>
                            <span class="inline-block transition" :class="open['{{ $section['key'] }}'] && 'rotate-180'">
                                <x-admin.icon name="chevron-down" class="h-5 w-5 text-slate-400" />
                            </span>
                        </div>
                    </button>

                    <div
                        x-show="open['{{ $section['key'] }}']"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-cloak
                        class="border-t border-erp-border"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[32rem] text-sm">
                                <thead class="bg-slate-50/90 text-[11px] uppercase text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2 text-left">{{ __('Account') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('Debit') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('Credit') }}</th>
                                        <th class="px-4 py-2 text-right">{{ $tableMode === 'extended' ? __('Debit bal.') : __('Balance') }}</th>
                                        @if ($tableMode === 'extended')
                                            <th class="px-4 py-2 text-right">{{ __('Credit bal.') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section['rows'] as $index => $row)
                                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/40' }} border-b border-erp-border/40 hover:bg-erp-accent/5">
                                            <td class="px-4 py-2">
                                                <span class="font-mono text-xs text-slate-500">{{ $row['account_code'] }}</span>
                                                <span class="text-erp-primary">{{ $row['account_name'] }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums">{{ $row['period_debit'] > 0 ? number_format($row['period_debit'], 2) : '—' }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums">{{ $row['period_credit'] > 0 ? number_format($row['period_credit'], 2) : '—' }}</td>
                                            @if ($tableMode === 'extended')
                                                <td class="px-4 py-2 text-right tabular-nums">{{ ($row['debit_balance'] ?? 0) > 0 ? number_format($row['debit_balance'], 2) : '—' }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ ($row['credit_balance'] ?? 0) > 0 ? number_format($row['credit_balance'], 2) : '—' }}</td>
                                            @else
                                                <td class="px-4 py-2 text-right font-medium tabular-nums">{{ number_format($rowNetBalance($row), 2) }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-slate-100/80 font-semibold text-erp-primary">
                                    <tr>
                                        <td class="px-4 py-2">{{ __('Section subtotal') }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($section['period_debit'], 2) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($section['period_credit'], 2) }}</td>
                                        @if ($tableMode === 'extended')
                                            <td colspan="2" class="px-4 py-2 text-right tabular-nums">{{ number_format($section['net_balance'], 2) }}</td>
                                        @else
                                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($section['net_balance'], 2) }}</td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Detailed view --}}
        <div x-show="view === 'detailed'" x-cloak>
            <div class="mb-3 flex justify-end">
                <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" x-model="compact" class="rounded border-erp-border text-erp-accent focus:ring-erp-accent">
                    {{ __('Compact mode') }}
                </label>
            </div>

            <x-admin.card class="overflow-hidden p-0">
                <div class="max-h-[min(70vh,48rem)] overflow-auto">
                    <table class="w-full min-w-[40rem] text-sm" :class="compact && 'text-xs'">
                        <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
                            <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-3" :class="compact && 'py-2'">{{ __('Account') }}</th>
                                <th class="px-4 py-3 text-right" :class="compact && 'py-2'">{{ __('Debit') }}</th>
                                <th class="px-4 py-3 text-right" :class="compact && 'py-2'">{{ __('Credit') }}</th>
                                @if ($tableMode === 'extended')
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'">{{ __('Debit bal.') }}</th>
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'">{{ __('Credit bal.') }}</th>
                                @else
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'">{{ __('Balance') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastSection = null; @endphp
                            @foreach ($detailedRows as $index => $row)
                                @php
                                    $sectionKey = $resolveSection($row['account_code']);
                                    $sectionLabel = $sectionDefs[$sectionKey]['label'] ?? __('Other');
                                @endphp
                                @if ($sectionKey !== $lastSection)
                                    <tr class="bg-slate-100/90">
                                        <td colspan="{{ $tableMode === 'extended' ? 5 : 4 }}" class="px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            {{ $sectionLabel }}
                                        </td>
                                    </tr>
                                    @php $lastSection = $sectionKey; @endphp
                                @endif
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }} border-b border-erp-border/30 transition-colors hover:bg-erp-accent/5">
                                    <td class="px-4 tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">
                                        <span class="font-mono text-xs text-slate-500">{{ $row['account_code'] }}</span>
                                        <span class="text-erp-primary">{{ $row['account_name'] }}</span>
                                    </td>
                                    <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">{{ $row['period_debit'] > 0 ? number_format($row['period_debit'], 2) : '—' }}</td>
                                    <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">{{ $row['period_credit'] > 0 ? number_format($row['period_credit'], 2) : '—' }}</td>
                                    @if ($tableMode === 'extended')
                                        <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">{{ ($row['debit_balance'] ?? 0) > 0 ? number_format($row['debit_balance'], 2) : '—' }}</td>
                                        <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">{{ ($row['credit_balance'] ?? 0) > 0 ? number_format($row['credit_balance'], 2) : '—' }}</td>
                                    @else
                                        <td class="px-4 text-right font-medium tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">{{ number_format($rowNetBalance($row), 2) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-slate-100 font-semibold text-erp-primary shadow-[0_-1px_0_0_var(--color-erp-border)]">
                            <tr>
                                <td class="px-4 py-3">{{ __('Totals') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($report['total_debit'], 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($report['total_credit'], 2) }}</td>
                                <td colspan="{{ $tableMode === 'extended' ? 2 : 1 }}" class="px-4 py-3 text-right">
                                    @if ($report['is_balanced'])
                                        <span class="text-emerald-600">{{ __('Balanced') }}</span>
                                    @else
                                        <span class="text-amber-600">{{ __('Out of balance') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-admin.card>
        </div>
    @endif
</div>
