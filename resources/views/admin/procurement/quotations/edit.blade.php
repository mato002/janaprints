<x-admin-layout :title="__('Edit Supplier Quotation')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Supplier Quotations'), 'url' => route('admin.procurement.quotations.index')], ['label' => $quotation->quotation_number]]">
    <x-admin.page-header :title="__('Edit supplier quotation')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.quotations.update', $quotation) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="erp-form-grid">
                <div>
                    <x-input-label for="vendor_id" :value="__('Vendor')" />
                    <select id="vendor_id" name="vendor_id" class="erp-select mt-1 w-full" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected($quotation->vendor_id === $vendor->id)>{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="quotation_date" :value="__('Quotation date')" />
                    <x-text-input id="quotation_date" name="quotation_date" type="date" class="mt-1 block w-full" value="{{ $quotation->quotation_date?->toDateString() }}" required />
                </div>
                <div>
                    <x-input-label for="valid_until" :value="__('Valid until')" />
                    <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full" min="{{ now()->toDateString() }}" value="{{ $quotation->valid_until?->toDateString() }}" />
                </div>
            </div>
            @include('admin.procurement.partials.line-items-form', [
                'items' => $items,
                'mode' => 'order',
                'existing' => $quotation->items,
            ])
            <x-primary-button>{{ __('Update quotation') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
