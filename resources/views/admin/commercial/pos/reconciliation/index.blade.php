<x-admin-layout :title="__('Cash Reconciliation')">
    <x-admin.page-header :title="__('Cash Reconciliation')" :description="__('End-of-day cash counts, variances, and approval workflow.')">
        <x-slot name="actions">
            <a href="{{ route('admin.commercial.pos.reconciliation.history') }}" class="erp-btn-secondary">{{ __('History') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    @include('admin.commercial.reports.sales.partials.readiness-table', [
        'readiness' => $readiness,
        'report_ready' => $report_ready,
        'context' => __('cash reconciliation'),
    ])

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-7">
        <x-admin.kpi-widget :label="__('Today\'s Reconciliations')" :value="$stats['today_count']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Pending Reviews')" :value="$stats['pending_reviews']" icon="clock" />
        <x-admin.kpi-widget :label="__('Variance Cases')" :value="$stats['variance_cases']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Approved Today')" :value="$stats['approved_today']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Total Cash')" :value="number_format($stats['total_cash'], 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Total M-Pesa')" :value="number_format($stats['total_mpesa'], 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Total Card')" :value="number_format($stats['total_card'], 2)" icon="credit-card" />
    </div>

    <x-admin.card class="mb-6">
        <form method="GET" action="{{ route('admin.commercial.pos.reconciliation.index') }}" class="grid gap-3 md:grid-cols-4">
            <div>
                <label class="text-[11px] text-slate-500" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="erp-input mt-1 w-full">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (App\Enums\PosReconciliationStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="variance_type">{{ __('Variance') }}</label>
                <select id="variance_type" name="variance_type" class="erp-input mt-1 w-full">
                    <option value="">{{ __('All variances') }}</option>
                    @foreach (App\Enums\PosVarianceType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(($filters['variance_type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>
                    @endforeach
                </select>
            </div>
            @if ($branches->isNotEmpty())
                <div>
                    <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
                    <select id="branch_id" name="branch_id" class="erp-input mt-1 w-full">
                        <option value="">{{ __('All branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="text-[11px] text-slate-500" for="cashier_id">{{ __('Cashier') }}</label>
                <select id="cashier_id" name="cashier_id" class="erp-input mt-1 w-full">
                    <option value="">{{ __('All cashiers') }}</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Reconciliation') }}</th>
                        <th class="px-3 py-2">{{ __('Session') }}</th>
                        <th class="px-3 py-2">{{ __('Cashier') }}</th>
                        <th class="px-3 py-2">{{ __('Branch') }}</th>
                        <th class="px-3 py-2">{{ __('Expected') }}</th>
                        <th class="px-3 py-2">{{ __('Actual') }}</th>
                        <th class="px-3 py-2">{{ __('Variance') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reconciliations as $reconciliation)
                        <tr class="border-b border-erp-border/60">
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.commercial.pos.reconciliation.show', $reconciliation) }}" class="font-medium text-erp-accent">{{ $reconciliation->reconciliation_number }}</a>
                            </td>
                            <td class="px-3 py-2">{{ $reconciliation->session?->session_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $reconciliation->cashier?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $reconciliation->branch?->name ?? '—' }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ number_format($reconciliation->expected_cash, 2) }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ number_format($reconciliation->actual_cash, 2) }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ number_format($reconciliation->variance, 2) }}</td>
                            <td class="px-3 py-2">{{ ucfirst(str_replace('_', ' ', $reconciliation->status->value)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">{{ __('No reconciliations found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reconciliations->links() }}</div>
    </x-admin.card>
</x-admin-layout>
