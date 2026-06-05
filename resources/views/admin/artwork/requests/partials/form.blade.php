@php($fields = $formFields ?? [])
@php($model = $request ?? null)

@if(($fields['customer_id']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Customer') }}</label>
    <select name="customer_id" class="erp-input w-full" @required($fields['customer_id']['required'] ?? true) @disabled($fields['customer_id']['read_only'] ?? false)>
        <option value="">{{ __('Select customer') }}</option>
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}" @selected(old('customer_id', $model?->customer_id) == $customer->id)>
                {{ $customer->company_name }} ({{ $customer->customer_code }})
            </option>
        @endforeach
    </select>
</div>
@endif

@if(($fields['quotation_id']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Quotation') }}</label>
    <select name="quotation_id" class="erp-input w-full" @required($fields['quotation_id']['required'] ?? false) @disabled($fields['quotation_id']['read_only'] ?? false)>
        <option value="">{{ __('None') }}</option>
        @foreach ($quotations as $quotation)
            <option value="{{ $quotation->id }}" @selected(old('quotation_id', $model?->quotation_id) == $quotation->id)>
                {{ $quotation->quotation_number }}
            </option>
        @endforeach
    </select>
</div>
@endif

@if(($fields['title']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Title') }}</label>
    <input type="text" name="title" class="erp-input w-full" value="{{ old('title', $model?->title ?? ($fields['title']['default'] ?? '')) }}" @required($fields['title']['required'] ?? true) @readonly($fields['title']['read_only'] ?? false)>
</div>
@endif

@if(($fields['description']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Description') }}</label>
    <textarea name="description" class="erp-input w-full" rows="3" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $model?->description ?? ($fields['description']['default'] ?? '')) }}</textarea>
</div>
@endif

@if(($fields['priority']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Priority') }}</label>
    <select name="priority" class="erp-input w-full" @required($fields['priority']['required'] ?? true) @disabled($fields['priority']['read_only'] ?? false)>
        @foreach ($priorities as $priority)
            <option value="{{ $priority->value }}" @selected(old('priority', $model?->priority?->value ?? ($fields['priority']['default'] ?? 'normal')) === $priority->value)>
                {{ ucfirst($priority->value) }}
            </option>
        @endforeach
    </select>
</div>
@endif

@if(($fields['due_date']['visible'] ?? true))
<div>
    <label class="erp-label">{{ __('Due date') }}</label>
    <input type="date" name="due_date" class="erp-input w-full" value="{{ old('due_date', $model?->due_date?->format('Y-m-d') ?? ($fields['due_date']['default'] ?? '')) }}" @required($fields['due_date']['required'] ?? false) @readonly($fields['due_date']['read_only'] ?? false)>
</div>
@endif

@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $model ?? null])
