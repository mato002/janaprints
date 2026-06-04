<x-admin-layout :title="__('POS')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('POS')]]">
    <x-admin.page-header :title="__('Point of Sale')" :description="__('Counter sales and walk-in checkout.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosSale::class)
                <a href="{{ route('admin.commercial.pos.create') }}" class="erp-btn-primary">{{ __('New sale') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Paid sales today')" :value="$stats['sales_today']" icon="cash" />
        <x-admin.kpi-widget :label="__('Revenue today')" :value="number_format($stats['revenue_today'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Held sales')" :value="$stats['held']" icon="clock" />
        <x-admin.kpi-widget :label="__('Draft carts')" :value="$stats['draft']" icon="document-text" />
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 font-medium">{{ __('Quick links') }}</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.commercial.pos.index') }}" class="erp-btn-secondary">{{ __('Daily sales') }}</a>
                <a href="{{ route('admin.commercial.pos.holds') }}" class="erp-btn-secondary">{{ __('Held sales') }}</a>
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
