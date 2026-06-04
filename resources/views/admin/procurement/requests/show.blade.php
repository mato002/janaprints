<x-admin-layout :title="$purchaseRequest->request_number" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Purchase Requests'), 'url' => route('admin.procurement.requests.index')], ['label' => $purchaseRequest->request_number]]">
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
    @can('create', App\Models\Procurement\Rfq::class)
        @if ($purchaseRequest->status === App\Enums\PurchaseRequestStatus::Approved)
            <x-admin.card class="mt-4">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Issue RFQ') }}</h3>
                <form method="POST" action="{{ route('admin.procurement.requests.rfq.store', $purchaseRequest) }}" class="mt-3 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-[12rem]">
                        <x-input-label for="closing_date" :value="__('Closing date')" />
                        <input type="date" id="closing_date" name="closing_date" class="erp-input mt-1 w-full" />
                    </div>
                    <div class="min-w-[20rem] flex-1">
                        <x-input-label for="vendor_ids" :value="__('Invite vendors')" />
                        <select id="vendor_ids" name="vendor_ids[]" class="erp-select mt-1 w-full" multiple required>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Create RFQ') }}</x-primary-button>
                </form>
            </x-admin.card>
        @endif
    @endcan
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
