@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'rows' => 3,
    'colSpan' => 2,
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
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        class="erp-input w-full"
        @required($required)
        @readonly($readonly)
        {{ $attributes }}
    >{{ $value }}</textarea>
</x-admin.form-field>
