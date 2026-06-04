<x-admin-layout :title="$purchaseRequest->request_number" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Purchase Requests'), 'url' => route('admin.procurement.requests.index')], ['label' => $purchaseRequest->request_number]]">
    <x-admin.page-header :title="$purchaseRequest->request_number" :description="str($purchaseRequest->status->value)->headline()">
        <x-slot name="actions">
            @can('update', $purchaseRequest)
                <a href="{{ route('admin.procurement.requests.edit', $purchaseRequest) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('submit', $purchaseRequest)
                <form method="POST" action="{{ route('admin.procurement.requests.submit', $purchaseRequest) }}">@csrf<button class="erp-btn-primary">{{ __('Submit') }}</button></form>
            @endcan
            @can('approve', $purchaseRequest)
                <form method="POST" action="{{ route('admin.procurement.requests.approve', $purchaseRequest) }}">@csrf<button class="erp-btn-primary">{{ __('Approve') }}</button></form>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Est. cost') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($purchaseRequest->items as $item)
                    <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->estimated_unit_cost, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
    @can('convert', $purchaseRequest)
        <x-admin.card class="mt-4">
            <form method="POST" action="{{ route('admin.procurement.requests.convert', $purchaseRequest) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[16rem]">
                    <x-input-label for="vendor_id" :value="__('Vendor')" />
                    <select id="vendor_id" name="vendor_id" class="erp-select mt-1 w-full" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>{{ __('Convert to PO') }}</x-primary-button>
            </form>
        </x-admin.card>
    @endcan
</x-admin-layout>
