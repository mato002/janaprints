<x-admin-layout :title="__('Edit quotation')" :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.show', $quotation)], ['label' => __('Edit')]]">
    <x-admin.card>
        <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}" class="space-y-6">@csrf @method('PUT')
            <p class="text-sm text-amber-700">{{ __('Saving creates revision :n → :next', ['n' => $quotation->revision_number, 'next' => $quotation->revision_number + 1]) }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="erp-label">{{ __('Customer') }}</label>
                    <select name="customer_id" class="erp-input" required>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected($quotation->customer_id == $c->id)>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Quotation date') }}</label>
                    <input type="date" name="quotation_date" class="erp-input" value="{{ old('quotation_date', $quotation->quotation_date->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Valid until') }}</label>
                    <input type="date" name="valid_until" class="erp-input" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('Currency') }}</label>
                    <input type="text" name="currency" class="erp-input" value="{{ old('currency', $quotation->currency) }}" maxlength="3" required>
                </div>
            </div>
            @include('admin.sales.quotations.partials.items-form', ['quotation' => $quotation])
            <x-primary-button>{{ __('Save & new revision') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
