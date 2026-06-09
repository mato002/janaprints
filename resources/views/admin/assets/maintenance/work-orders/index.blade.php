<x-admin-layout :title="__('Work Orders')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Work Orders')]]">
    <x-admin.page-header :title="__('Maintenance Work Orders')" :description="__('Preventive, corrective, and emergency maintenance.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\MaintenanceWorkOrder::class)
                <a href="{{ route('admin.assets.maintenance.work-orders.create') }}" class="erp-btn-primary">{{ __('New Work Order') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search work orders…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <select name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($priorities as $p)
                    <option value="{{ $p->value }}" @selected($filters['priority'] === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Work Order') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Type') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th><th>{{ __('Scheduled') }}</th></tr></thead>
                <tbody>
                    @forelse ($work_orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.assets.maintenance.work-orders.show', $order) }}" class="erp-link font-mono">{{ $order->work_order_no }}</a></td>
                            <td>{{ $order->asset?->asset_name }}</td>
                            <td>{{ $order->maintenance_type->label() }}</td>
                            <td><x-admin.status-badge :variant="$order->priority->badgeVariant()">{{ $order->priority->label() }}</x-admin.status-badge></td>
                            <td><x-admin.status-badge :variant="$order->status->badgeVariant()">{{ $order->status->label() }}</x-admin.status-badge></td>
                            <td>{{ $order->scheduled_for?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No work orders yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($work_orders->hasPages())<div class="mt-4">{{ $work_orders->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
