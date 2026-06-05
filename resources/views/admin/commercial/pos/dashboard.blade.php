<x-admin-layout :title="__('POS')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('POS')]]">
    <x-admin.page-header :title="__('Point of Sale')" :description="__('Counter sales and walk-in checkout.')">
        <x-slot name="actions">
            @can('counterSalesView', App\Models\Pos\PosSale::class)
                <a href="{{ route('admin.commercial.pos.counter-sales') }}" class="erp-btn-primary">{{ __('New sale') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.pos.partials.session-widget', ['sessionWidget' => $sessionWidget])

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Paid sales today')" :value="$stats['sales_today']" icon="cash" />
        <x-admin.kpi-widget :label="__('Revenue today')" :value="number_format($stats['revenue_today'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Held sales')" :value="$stats['held']" icon="clock" />
        <x-admin.kpi-widget :label="__('Draft carts')" :value="$stats['draft']" icon="document-text" />
    </div>

    <x-admin.card class="mb-6">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="font-medium">{{ __('Held sales queue') }}</h3>
            <a href="{{ route('admin.commercial.pos.holds') }}" class="text-sm font-medium text-erp-accent">{{ __('View all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Sale #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Created by') }}</th>
                        <th>{{ __('Hold time') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($heldQueue as $hold)
                        <tr>
                            <td class="font-mono">{{ $hold->sale?->sale_number }}</td>
                            <td>{{ $hold->customer?->company_name ?? __('Walk-in') }}</td>
                            <td>{{ $hold->cashier?->name }}</td>
                            <td>{{ $hold->held_at->format('Y-m-d H:i') }}</td>
                            <td class="tabular-nums">{{ number_format($hold->sale?->total_amount ?? 0, 2) }}</td>
                            <td class="erp-table-actions-col">
                                @can('update', $hold->sale)
                                    <a href="{{ route('admin.commercial.pos.resume', $hold->sale) }}" class="text-sm font-medium text-erp-accent">{{ __('Resume') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('No held sales in queue.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 font-medium">{{ __('Quick links') }}</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.commercial.pos.index') }}" class="erp-btn-secondary">{{ __('Daily sales') }}</a>
                <a href="{{ route('admin.commercial.pos.holds') }}" class="erp-btn-secondary">{{ __('Held sales') }}</a>
                @can('viewAny', App\Models\Pos\PosSession::class)
                    <a href="{{ route('admin.commercial.pos.sessions.index') }}" class="erp-btn-secondary">{{ __('POS Sessions') }}</a>
                @endcan
            </div>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 font-medium">{{ __('Recent sales') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($recent as $sale)
                    <li class="flex justify-between gap-2 border-b border-erp-border py-2">
                        <a href="{{ route('admin.commercial.pos.show', $sale) }}" class="font-medium text-erp-accent">{{ $sale->sale_number }}</a>
                        <span class="tabular-nums">{{ number_format($sale->total_amount, 2) }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No sales yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
