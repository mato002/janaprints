<x-admin-layout :title="__('POS Sessions')">
    <x-admin.page-header :title="__('POS Sessions')" :description="__('Cashier session control, floats, and cash variance.')">
        <x-slot name="actions">
            @can('open', App\Models\Pos\PosSession::class)
                <a href="{{ route('admin.commercial.pos.sessions.create') }}" class="erp-btn-primary">{{ __('Open session') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
        <x-admin.kpi-widget :label="__('Open Sessions')" :value="$stats['open_sessions']" icon="clock" />
        <x-admin.kpi-widget :label="__('Closed Today')" :value="$stats['closed_today']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Total Sales Today')" :value="$stats['sales_today']" icon="cash" />
        <x-admin.kpi-widget :label="__('Expected Cash')" :value="number_format($stats['expected_cash'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Actual Cash')" :value="number_format($stats['actual_cash'], 2)" icon="scale" />
        <x-admin.kpi-widget :label="__('Variance')" :value="number_format($stats['variance'], 2)" icon="chart-bar" />
    </div>

    <x-admin.card :padding="false" class="mb-6">
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

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Session') }}</th>
                        <th class="px-3 py-2">{{ __('Cashier') }}</th>
                        <th class="px-3 py-2">{{ __('Branch') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Opened') }}</th>
                        <th class="px-3 py-2">{{ __('Expected') }}</th>
                        <th class="px-3 py-2">{{ __('Actual') }}</th>
                        <th class="px-3 py-2">{{ __('Variance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="border-b border-erp-border/60">
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.commercial.pos.sessions.show', $session) }}" class="font-medium text-erp-accent">{{ $session->session_number }}</a>
                            </td>
                            <td class="px-3 py-2">{{ $session->cashier?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $session->branch?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ ucfirst($session->status->value) }}</td>
                            <td class="px-3 py-2">{{ $session->opened_at?->format('d M Y H:i') }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $session->expected_cash !== null ? number_format($session->expected_cash, 2) : '—' }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—' }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $session->variance !== null ? number_format($session->variance, 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">{{ __('No sessions found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $sessions->links() }}</div>
    </x-admin.card>
</x-admin-layout>
