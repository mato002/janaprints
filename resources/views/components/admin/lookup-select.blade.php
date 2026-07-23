@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hidden' => false,
    'createRoute' => null,
    'createQuery' => [],
    'refreshRoute' => null,
    'permission' => null,
    'modalTitle' => null,
    'optionLabelKey' => 'name',
    'optionValueKey' => 'id',
    'selectClass' => 'erp-select mt-1',
    'emptyOption' => true,
    'emptyLabel' => null,
    'scopeCompanyField' => null,
    'scopeBranchField' => null,
    'scopeCustomerField' => null,
])

@php
    $normalizedOptions = collect($options)->map(function ($option) use ($optionLabelKey, $optionValueKey) {
        if (is_array($option)) {
            return [
                'value' => $option['value'] ?? $option[$optionValueKey] ?? '',
                'label' => $option['label'] ?? $option[$optionLabelKey] ?? '',
            ];
        }

        return [
            'value' => data_get($option, $optionValueKey),
            'label' => (string) data_get($option, $optionLabelKey),
        ];
    })->values()->all();

    $selectedValue = old($name, $value);
    $isReadOnly = (bool) $readonly;
    $isDisabled = (bool) $disabled || $isReadOnly;
    $permissions = collect(explode('|', (string) $permission))->filter()->values()->all();
    $canCreate = $createRoute
        && Route::has($createRoute)
        && ($permissions === [] || collect($permissions)->contains(fn ($perm) => auth()->user()?->can($perm)))
        && ! $isDisabled
        && ! $isReadOnly;
    $createUrl = $canCreate
        ? route($createRoute, is_array($createQuery) ? array_filter($createQuery, fn ($value) => $value !== null && $value !== '') : [])
        : null;
    $refreshUrl = $refreshRoute && Route::has($refreshRoute) ? route($refreshRoute) : null;
    $fieldId = $attributes->get('id', $name);
@endphp

@if (! $hidden)
<div
    x-data="erpLookupCreate({
        name: @js($name),
        selected: @js($selectedValue !== null && $selectedValue !== '' ? (string) $selectedValue : ''),
        options: @js($normalizedOptions),
        createUrl: @js($createUrl),
        refreshUrl: @js($refreshUrl),
        modalTitle: @js($modalTitle ?? $label),
        scopeCompanyField: @js($scopeCompanyField),
        scopeBranchField: @js($scopeBranchField),
        scopeCustomerField: @js($scopeCustomerField),
    })"
    {{ $attributes->class(['erp-lookup-select']) }}
>
    @if ($label)
        <x-input-label :for="$fieldId" :value="$label" :required="$required && ! $isReadOnly" />
    @endif
    <div class="erp-lookup-select__row mt-1 flex items-stretch gap-2">
        <select
            id="{{ $fieldId }}"
            name="{{ $name }}"
            class="{{ $selectClass }} erp-lookup-select__input min-w-0 flex-1"
            x-model="selected"
            data-empty-option="{{ $emptyOption ? '1' : '0' }}"
            @if ($placeholder) data-placeholder="{{ $emptyLabel ?? $placeholder ?? __('Select') }}" @endif
            @required($required && ! $isReadOnly)
            @disabled($isDisabled)
        >
            @if ($emptyOption)
                <option value="">{{ $emptyLabel ?? $placeholder ?? __('Select') }}</option>
            @endif
            @foreach ($normalizedOptions as $option)
                <option value="{{ $option['value'] }}" @selected((string) ($selectedValue ?? '') === (string) $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        @if ($canCreate)
            <button
                type="button"
                class="erp-lookup-select__add"
                x-on:click.stop.prevent="openCreate($event)"
                :aria-label="@js(__('Add new').' '.($modalTitle ?? $label ?? $name))"
                :title="@js(__('Add new').' '.($modalTitle ?? $label ?? $name))"
            >
                <span aria-hidden="true">+</span>
            </button>
        @endif
    </div>
    <x-admin.field-error :name="$name" />
</div>
@endif
