<x-admin-layout :title="__('Virtual locations')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Store Operations'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'store-operations'])],
    ['label' => __('Virtual locations')],
]">
    @php
        $roleMeta = [
            \App\Enums\VirtualWarehouseRole::RawMaterial->value => [
                'icon' => 'cube',
                'accent' => 'bg-slate-100 text-slate-700',
                'ring' => 'ring-slate-200',
            ],
            \App\Enums\VirtualWarehouseRole::Wip->value => [
                'icon' => 'cog',
                'accent' => 'bg-amber-100 text-amber-800',
                'ring' => 'ring-amber-200',
            ],
            \App\Enums\VirtualWarehouseRole::FinishedGoods->value => [
                'icon' => 'shopping-bag',
                'accent' => 'bg-emerald-100 text-emerald-800',
                'ring' => 'ring-emerald-200',
            ],
            \App\Enums\VirtualWarehouseRole::InTransit->value => [
                'icon' => 'truck',
                'accent' => 'bg-sky-100 text-sky-800',
                'ring' => 'ring-sky-200',
            ],
            \App\Enums\VirtualWarehouseRole::Quarantine->value => [
                'icon' => 'shield-check',
                'accent' => 'bg-rose-100 text-rose-800',
                'ring' => 'ring-rose-200',
            ],
        ];

        $totalItems = collect($locations)->sum('item_count');
        $totalValue = collect($locations)->sum('total_value');
        $locationsWithStock = collect($locations)->filter(fn (array $row) => $row['item_count'] > 0)->count();
    @endphp

    <x-admin.page-header
        :title="__('Virtual locations')"
        :description="__('Logical inventory buckets for finished goods, in-transit, and quarantine. Raw materials stay in physical stores; WIP is accounting-only. Stock truth remains in inventory movements.')"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.inventory.movements.index') }}" class="erp-btn-secondary">{{ __('All movements') }}</a>
            @can('inventory.virtual-locations.manage')
                <form method="POST" action="{{ route('admin.inventory.virtual-locations.ensure-defaults') }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Verify defaults') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.kpi-widget :label="__('Virtual buckets')" :value="number_format(count($locations))" icon="view-grid" />
        <x-admin.kpi-widget :label="__('Buckets with stock')" :value="number_format($locationsWithStock)" icon="archive" />
        <x-admin.kpi-widget :label="__('Items with stock')" :value="number_format($totalItems)" icon="cube" />
        <x-admin.kpi-widget :label="__('Total est. value')" :value="number_format($totalValue, 2)" icon="currency-dollar" />
    </div>

    <x-admin.alert variant="info" class="mt-6">
        <div class="flex items-start gap-3">
            <x-admin.icon name="information-circle" class="mt-0.5 h-5 w-5 shrink-0" />
            <div>
                <p class="font-medium">{{ __('How virtual locations work') }}</p>
                <p class="mt-1 text-sky-800/90">
                    {{ __('Physical stores hold raw materials. Virtual buckets track finished goods, in-transit, and quarantine balances from the movement ledger. WIP is posted to the general ledger only—it does not hold inventory quantity here.') }}
                </p>
            </div>
        </div>
    </x-admin.alert>

    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($locations as $location)
            @php
                /** @var array{role: \App\Enums\VirtualWarehouseRole, warehouse: ?\App\Models\Inventory\Warehouse, item_count: int, total_value: float, last_movement_at: ?\Illuminate\Support\Carbon, empty_message: ?string} $location */
                $warehouse = $location['warehouse'];
                $role = $location['role'];
                $meta = $roleMeta[$role->value] ?? ['icon' => 'cube', 'accent' => 'bg-slate-100 text-slate-700', 'ring' => 'ring-slate-200'];
                $hasStock = $location['item_count'] > 0;
            @endphp

            <x-admin.card :hover="true" :padding="false" class="flex h-full flex-col overflow-hidden">
                <div class="border-b border-erp-border px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $meta['accent'] }} {{ $meta['ring'] }}">
                                <x-admin.icon :name="$meta['icon']" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold text-erp-primary">{{ $role->label() }}</h2>
                                @if ($warehouse)
                                    <p class="mt-0.5 truncate font-mono text-xs text-slate-500">{{ $warehouse->code }}</p>
                                @else
                                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Not provisioned') }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($role->isAccountingOnlyLayer())
                            <x-admin.status-badge variant="warning">{{ __('Accounting only') }}</x-admin.status-badge>
                        @else
                            <x-admin.status-badge variant="info">{{ __('Virtual') }}</x-admin.status-badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-4 px-4 py-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2.5">
                            <p class="text-xs font-medium text-slate-500">{{ __('Items with stock') }}</p>
                            <p class="mt-1 text-lg font-semibold tabular-nums text-erp-primary">{{ number_format($location['item_count']) }}</p>
                        </div>
                        <div class="rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2.5">
                            <p class="text-xs font-medium text-slate-500">{{ __('Est. value') }}</p>
                            <p class="mt-1 text-lg font-semibold tabular-nums text-erp-primary">{{ number_format($location['total_value'], 2) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 rounded-lg border border-dashed border-erp-border px-3 py-2.5 text-sm">
                        <span class="text-xs font-medium text-slate-500">{{ __('Last movement') }}</span>
                        <span class="font-medium text-slate-700">
                            @if ($location['last_movement_at'])
                                {{ $location['last_movement_at']->format('d M Y') }}
                            @else
                                <span class="text-slate-400">{{ __('None yet') }}</span>
                            @endif
                        </span>
                    </div>

                    @if ($location['empty_message'])
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs leading-relaxed text-amber-900">
                            {{ $location['empty_message'] }}
                        </p>
                    @elseif ($hasStock)
                        <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-800">
                            {{ __('Active stock recorded in the movement ledger for this bucket.') }}
                        </p>
                    @endif
                </div>

                @if ($warehouse)
                    <div class="border-t border-erp-border bg-erp-page/40 px-4 py-3">
                        <a
                            href="{{ route('admin.inventory.movements.index', ['warehouse_id' => $warehouse->id]) }}"
                            class="inline-flex items-center gap-2 text-sm font-medium text-erp-accent transition hover:text-erp-accent/80"
                        >
                            <x-admin.icon name="switch-horizontal" class="h-4 w-4" />
                            {{ __('View movements') }}
                        </a>
                    </div>
                @endif
            </x-admin.card>
        @endforeach
    </div>
</x-admin-layout>
