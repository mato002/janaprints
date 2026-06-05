<x-admin-layout :title="$session->session_number">
    <x-admin.page-header :title="$session->session_number" :description="__('POS session detail and sales activity.')">
        <x-slot name="actions">
            @can('close', $session)
                @if ($session->status === App\Enums\PosSessionStatus::Open)
                    <a href="{{ route('admin.commercial.pos.sessions.close', $session) }}" class="{{ $can_close ? 'erp-btn-primary' : 'erp-btn-secondary opacity-75' }}">{{ __('Close session') }}</a>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-5">
        <x-admin.kpi-widget :label="__('Sales Count')" :value="$metrics['sales_count']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Cash Sales')" :value="number_format($metrics['cash_sales'], 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('M-Pesa Sales')" :value="number_format($metrics['mpesa_sales'], 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Card Sales')" :value="number_format($metrics['card_sales'], 2)" icon="credit-card" />
        <x-admin.kpi-widget :label="__('Refunds')" :value="$metrics['refunds']" icon="switch-horizontal" />
    </div>

    @if ($session->status === App\Enums\PosSessionStatus::Open)
        <div class="mb-6">
            @include('admin.commercial.pos.sessions.partials.closure-checklist', ['governance' => $governance])
        </div>
    @endif

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Session Information') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd class="font-medium">{{ $session->cashier?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Branch') }}</dt><dd class="font-medium">{{ $session->branch?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium">{{ ucfirst($session->status->value) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Opened') }}</dt><dd>{{ $session->opened_at?->format('d M Y H:i') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Opening Float') }}</dt><dd class="tabular-nums">{{ number_format($session->opening_float, 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Opening Cash') }}</dt><dd class="tabular-nums">{{ number_format($session->opening_cash, 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Expected Closing Cash') }}</dt><dd class="tabular-nums">{{ number_format($metrics['expected_closing_cash'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Actual Closing Cash') }}</dt><dd class="tabular-nums">{{ $session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Variance') }}</dt><dd class="tabular-nums">{{ $session->variance !== null ? number_format($session->variance, 2) : '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Sales List') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($sales as $sale)
                    <li class="flex justify-between gap-2 border-b border-erp-border py-2">
                        <a href="{{ route('admin.commercial.pos.show', $sale) }}" class="font-medium text-erp-accent">{{ $sale->sale_number }}</a>
                        <span class="tabular-nums">{{ number_format($sale->total_amount, 2) }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No sales in this session yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>

    @if ($can_audit)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Audit Trail') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($auditTrail as $log)
                    <li class="border-b border-erp-border/60 py-2">
                        <span class="font-medium">{{ ucfirst($log->action) }}</span>
                        <span class="text-slate-500">— {{ $log->created_at?->format('d M Y H:i') }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No audit entries yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
