<x-admin-layout :title="__('Movements')">
    <x-admin.page-header :title="__('Inventory movements')" :description="__('Audit trail — source of stock truth.')" />
    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Item') }}</th><th>{{ __('Warehouse') }}</th><th>{{ __('Type') }}</th><th>{{ __('Qty') }}</th></tr></thead>
            <tbody>
                @foreach ($movements as $m)
                    <tr>
                        <td>{{ $m->movement_date->format('Y-m-d') }}</td>
                        <td>{{ $m->item?->sku }}</td>
                        <td>{{ $m->warehouse?->name }}</td>
                        <td>{{ $m->movement_type->value }}</td>
                        <td>{{ $m->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $movements->links() }}
    </x-admin.card>
</x-admin-layout>
