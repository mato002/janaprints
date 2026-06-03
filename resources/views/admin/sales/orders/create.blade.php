<x-admin-layout :title="__('New sales order')" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('Create from accepted quotation')" :description="__('Requires approved artwork linked to the quotation.')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.sales-orders.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @php($fields = $formFields ?? [])
            @if(($fields['quotation_id']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Quotation') }}</label>
                <select name="quotation_id" class="erp-input w-full" @required($fields['quotation_id']['required'] ?? true) @disabled($fields['quotation_id']['read_only'] ?? false)>
                    <option value="">{{ __('Select quotation') }}</option>
                    @foreach ($eligibleQuotations as $quotation)
                        <option value="{{ $quotation->id }}" @selected(old('quotation_id') == $quotation->id)>
                            {{ $quotation->quotation_number }} — {{ $quotation->customer?->company_name }}
                        </option>
                    @endforeach
                </select>
                @if ($eligibleQuotations->isEmpty())
                    <p class="text-sm text-slate-500 mt-2">{{ __('No accepted quotations available for conversion.') }}</p>
                @endif
            </div>
            @endif
            <button type="submit" class="erp-btn-primary" @disabled($eligibleQuotations->isEmpty())>{{ __('Create sales order') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
