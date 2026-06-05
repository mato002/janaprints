@props(['form', 'canManage', 'position' => 'bottom'])

@if ($canManage)
    <div
        @class([
            'border-erp-border bg-violet-50/60 px-5 py-4 sm:px-6',
            'border-b' => $position === 'top',
            'border-t' => $position === 'bottom',
        ])
        id="add-custom-field"
    >
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('Add custom field') }}</h3>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Use lowercase keys with underscores (e.g. tax_id). Fill in the fields below, then click Save form settings.') }}
                </p>
            </div>
            @if ($position === 'bottom')
                <a href="#add-custom-field" class="text-xs font-medium text-erp-accent hover:underline">{{ __('Jump here') }}</a>
            @endif
        </div>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-input-label for="add_field_key_{{ $form['form_key'] }}" :value="__('Field key')" />
                <input
                    type="text"
                    id="add_field_key_{{ $form['form_key'] }}"
                    name="forms[{{ $form['form_key'] }}][add_field][field_key]"
                    class="erp-input mt-1 w-full font-mono text-sm"
                    placeholder="custom_field"
                    pattern="[a-z][a-z0-9_]*"
                >
            </div>
            <div>
                <x-input-label for="add_field_label_{{ $form['form_key'] }}" :value="__('Label')" />
                <input
                    type="text"
                    id="add_field_label_{{ $form['form_key'] }}"
                    name="forms[{{ $form['form_key'] }}][add_field][label]"
                    class="erp-input mt-1 w-full"
                    placeholder="{{ __('Display label') }}"
                >
            </div>
            <div>
                <x-input-label for="add_field_type_{{ $form['form_key'] }}" :value="__('Type')" />
                <select id="add_field_type_{{ $form['form_key'] }}" name="forms[{{ $form['form_key'] }}][add_field][type]" class="erp-select mt-1 w-full">
                    <option value="text">{{ __('Text') }}</option>
                    <option value="email">{{ __('Email') }}</option>
                    <option value="number">{{ __('Number') }}</option>
                    <option value="date">{{ __('Date') }}</option>
                    <option value="textarea">{{ __('Textarea') }}</option>
                    <option value="select">{{ __('Select') }}</option>
                    <option value="checkbox">{{ __('Checkbox') }}</option>
                </select>
            </div>
            <div class="flex items-end">
                <p class="text-xs text-slate-500">{{ __('New fields appear in the table above after saving.') }}</p>
            </div>
        </div>
    </div>
@endif
