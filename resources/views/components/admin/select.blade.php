@props([
    'name',
    'label' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'colSpan' => 1,
])

<x-admin.form-field
    :name="$name"
    :label="$label"
    :required="$required"
    :visible="$visible"
    :readonly="$readonly"
    :help="$help"
    :colSpan="$colSpan"
>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        class="erp-select w-full"
        @required($required)
        @disabled($readonly)
        {{ $attributes }}
    >
        {{ $slot }}
    </select>
</x-admin.form-field>
