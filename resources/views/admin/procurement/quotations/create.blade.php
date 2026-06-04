<x-admin-layout :title="__('Create Supplier Quotation')" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Supplier Quotations'), 'url' => route('admin.procurement.quotations.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Create supplier quotation')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.quotations.store') }}" class="space-y-6">
            @csrf
            <div class="erp-form-grid">
                <div>
                    <x-input-label for="vendor_id" :value="__('Vendor')" />
                    <select id="vendor_id" name="vendor_id" class="erp-select mt-1 w-full" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="quotation_date" :value="__('Quotation date')" />
                    <x-text-input id="quotation_date" name="quotation_date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                </div>
            </div>
            @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'order'])
            <x-primary-button>{{ __('Save quotation') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
