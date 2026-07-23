<x-admin-layout :title="__('Cash Reconciliation')">
    @include('admin.commercial.pos.partials.desk-mode-nav', ['activePosView' => \App\Support\Commercial\PosDeskViews::RECON])

    <x-admin.page-header :title="__('Cash Reconciliation')" :description="__('End-of-day cash counts, variances, and approval workflow.')">
        <x-slot name="secondary">
            <a href="{{ route('admin.commercial.pos.reconciliation.history') }}" class="erp-btn-secondary">{{ __('History') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.kpi-strip class="xl:grid-cols-7">
        <x-admin.kpi-widget :label="__('Today\'s Reconciliations')" :value="$stats['today_count']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Pending Reviews')" :value="$stats['pending_reviews']" icon="clock" />
        <x-admin.kpi-widget :label="__('Variance Cases')" :value="$stats['variance_cases']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Approved Today')" :value="$stats['approved_today']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Total Cash')" :value="number_format($stats['total_cash'], 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Total M-Pesa')" :value="number_format($stats['total_mpesa'], 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Total Card')" :value="number_format($stats['total_card'], 2)" icon="credit-card" />
    </x-admin.kpi-strip>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.commercial.pos.reconciliation.index')" :reset-url="route('admin.commercial.pos.reconciliation.index')">
            <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PosReconciliationStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </select>
            <select id="variance_type" name="variance_type" class="erp-toolbar-select" aria-label="{{ __('Variance') }}">
                <option value="">{{ __('All variances') }}</option>
                @foreach (App\Enums\PosVarianceType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(($filters['variance_type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>
                @endforeach
            </select>
            @if ($branches->isNotEmpty())
                <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select id="cashier_id" name="cashier_id" class="erp-toolbar-select" aria-label="{{ __('Cashier') }}">
                <option value="">{{ __('All cashiers') }}</option>
                @foreach ($cashiers as $cashier)
                    <option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->name }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search reconciliations…')"
        export-filename="pos-reconciliations"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Reconciliation') }}</th>
                <th scope="col">{{ __('Session') }}</th>
                <th scope="col">{{ __('Cashier') }}</th>
                <th scope="col">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Expected') }}</th>
                <th scope="col">{{ __('Actual') }}</th>
                <th scope="col">{{ __('Variance') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($reconciliations as $reconciliation)
                @php
                    $search = strtolower($reconciliation->reconciliation_number.' '.($reconciliation->session?->session_number ?? '').' '.($reconciliation->cashier?->name ?? '').' '.($reconciliation->branch?->name ?? '').' '.$reconciliation->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-medium">{{ $reconciliation->reconciliation_number }}</td>
                    <td>{{ $reconciliation->session?->session_number ?? '—' }}</td>
                    <td>{{ $reconciliation->cashier?->name ?? '—' }}</td>
                    <td>{{ $reconciliation->branch?->name ?? '—' }}</td>
                    <td class="tabular-nums">{{ number_format($reconciliation->expected_cash, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($reconciliation->actual_cash, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($reconciliation->variance, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$reconciliation->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.pos.reconciliation.show', $reconciliation)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No reconciliations found')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$reconciliations" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
