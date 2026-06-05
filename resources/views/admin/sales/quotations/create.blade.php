<x-admin-layout :title="__('Create quotation')" :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => __('Create')]]">
    <x-admin.card>
        <form method="POST" action="{{ route('admin.quotations.store') }}" class="space-y-6">@csrf
            @php($fields = $formFields ?? [])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if(($fields['customer_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ __('Customer') }}</label>
                    <select name="customer_id" class="erp-input" @required($fields['customer_id']['required'] ?? true) @disabled($fields['customer_id']['read_only'] ?? false)>
                        @foreach ($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id', $presetCustomerId ?? null) == $c->id)>{{ $c->company_name }}</option>@endforeach
                    </select>
                </div>
                @endif
                @if(($fields['lead_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ __('Lead (optional)') }}</label>
                    <select name="lead_id" class="erp-input" @required($fields['lead_id']['required'] ?? false) @disabled($fields['lead_id']['read_only'] ?? false)>
                        <option value="">{{ __('None') }}</option>
                        @foreach ($leads as $l)<option value="{{ $l->id }}">{{ $l->lead_name }}</option>@endforeach
                    </select>
                </div>
                @endif
                @if(($fields['quotation_date']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ __('Quotation date') }}</label>
                    <input type="date" name="quotation_date" class="erp-input" value="{{ old('quotation_date', now()->toDateString()) }}" @required($fields['quotation_date']['required'] ?? true) @readonly($fields['quotation_date']['read_only'] ?? false)>
                </div>
                @endif
                @if(($fields['valid_until']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ __('Valid until') }}</label>
                    <input type="date" name="valid_until" class="erp-input" value="{{ old('valid_until', $fields['valid_until']['default'] ?? '') }}" @required($fields['valid_until']['required'] ?? false) @readonly($fields['valid_until']['read_only'] ?? false)>
                </div>
                @endif
                @if(($fields['currency']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ __('Currency') }}</label>
                    <input type="text" name="currency" class="erp-input" value="{{ old('currency', $fields['currency']['default'] ?? 'KES') }}" maxlength="3" @required($fields['currency']['required'] ?? true) @readonly($fields['currency']['read_only'] ?? false)>
                </div>
                @endif
            </div>
            <div>
                <h3 class="font-medium text-slate-800 mb-3">{{ __('Line items') }}</h3>
                @include('admin.sales.quotations.partials.items-form')
            </div>
            <x-primary-button>{{ __('Create quotation') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
