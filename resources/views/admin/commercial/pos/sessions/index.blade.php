<x-admin-layout :title="__('POS Sessions')">
    @include('admin.commercial.pos.partials.desk-mode-nav', ['activePosView' => \App\Support\Commercial\PosDeskViews::SESSIONS])

    <x-admin.page-header :title="__('POS Sessions')" :description="__('Cashier session control, floats, and cash variance.')">
        <x-slot name="actions">
            @can('open', App\Models\Pos\PosSession::class)
                <a href="{{ route('admin.commercial.pos.sessions.create') }}" class="erp-btn-primary">{{ __('Open session') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.kpi-strip>
        <x-admin.kpi-widget :label="__('Open Sessions')" :value="$stats['open_sessions']" icon="clock" />
        <x-admin.kpi-widget :label="__('Closed Today')" :value="$stats['closed_today']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Total Sales Today')" :value="$stats['sales_today']" icon="cash" />
        <x-admin.kpi-widget :label="__('Expected Cash')" :value="number_format($stats['expected_cash'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Actual Cash')" :value="number_format($stats['actual_cash'], 2)" icon="scale" />
        <x-admin.kpi-widget :label="__('Variance')" :value="number_format($stats['variance'], 2)" icon="chart-bar" />
    </x-admin.kpi-strip>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.commercial.pos.sessions.index')" :reset-url="route('admin.commercial.pos.sessions.index')">
            <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PosSessionStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
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
        :search-placeholder="__('Search sessions…')"
        export-filename="pos-sessions"
        :exportable="true"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Session') }}</th>
                <th scope="col">{{ __('Cashier') }}</th>
                <th scope="col">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Opened') }}</th>
                <th scope="col">{{ __('Expected') }}</th>
                <th scope="col">{{ __('Actual') }}</th>
                <th scope="col">{{ __('Variance') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($sessions as $session)
                @php
                    $search = strtolower($session->session_number.' '.($session->cashier?->name ?? '').' '.($session->branch?->name ?? '').' '.$session->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-medium">{{ $session->session_number }}</td>
                    <td>{{ $session->cashier?->name ?? '—' }}</td>
                    <td>{{ $session->branch?->name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$session->status->value" /></td>
                    <td class="whitespace-nowrap">{{ $session->opened_at?->format('d M Y H:i') }}</td>
                    <td class="tabular-nums">{{ $session->expected_cash !== null ? number_format($session->expected_cash, 2) : '—' }}</td>
                    <td class="tabular-nums">{{ $session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—' }}</td>
                    <td class="tabular-nums">{{ $session->variance !== null ? number_format($session->variance, 2) : '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.pos.sessions.show', $session)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="clock" :title="__('No sessions found')" :description="__('Open a cashier session to start taking sales.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$sessions" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
