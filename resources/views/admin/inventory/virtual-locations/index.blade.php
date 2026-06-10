<x-admin-layout :title="__('Virtual locations')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Store Operations'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'store-operations'])],
    ['label' => __('Virtual locations')],
]">
    <x-admin.page-header
        :title="__('Virtual locations')"
        :description="__('Logical inventory buckets for finished goods, in-transit, and quarantine. Raw materials stay in physical stores; WIP is accounting-only (general ledger). Stock truth remains in inventory movements.')"
    >
        @can('inventory.virtual-locations.manage')
            <form method="POST" action="{{ route('admin.inventory.virtual-locations.ensure-defaults') }}">
                @csrf
                <button type="submit" class="erp-btn-secondary">{{ __('Verify defaults') }}</button>
            </form>
        @endcan
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($locations as $location)
            @php
                /** @var array{role: \App\Enums\VirtualWarehouseRole, warehouse: ?\App\Models\Inventory\Warehouse, item_count: int, total_value: float, last_movement_at: ?\Illuminate\Support\Carbon, empty_message: ?string} $location */
                $warehouse = $location['warehouse'];
            @endphp
            <article class="erp-card flex flex-col gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">{{ $location['role']->label() }}</h2>
                        @if ($warehouse)
                            <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $warehouse->code }}</p>
                        @endif
                    </div>
                @if ($location['role']->isAccountingOnlyLayer())
                    <span class="erp-badge bg-amber-50 text-amber-900">{{ __('Accounting only') }}</span>
                @else
                    <span class="erp-badge bg-indigo-50 text-indigo-900">{{ __('Virtual') }}</span>
                @endif
                </div>

                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Items with stock') }}</dt>
                        <dd class="font-semibold text-slate-900">{{ number_format($location['item_count']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Est. value') }}</dt>
                        <dd class="font-semibold text-slate-900">{{ number_format($location['total_value'], 2) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs text-slate-500">{{ __('Last movement') }}</dt>
                        <dd class="text-slate-700">
                            @if ($location['last_movement_at'])
                                {{ $location['last_movement_at']->format('d M Y') }}
                            @else
                                {{ __('None yet') }}
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($location['empty_message'])
                    <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">{{ $location['empty_message'] }}</p>
                @endif

                @if ($warehouse)
                    <div class="mt-auto pt-1">
                        <a
                            href="{{ route('admin.inventory.movements.index', ['warehouse_id' => $warehouse->id]) }}"
                            class="erp-link text-sm"
                        >{{ __('View movements') }}</a>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</x-admin-layout>
