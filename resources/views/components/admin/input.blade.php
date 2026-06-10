@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
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
    <x-text-input
        :id="$name"
        :name="$name"
        :type="$type"
        class="block w-full"
        :value="$value"
        :required="$required"
        :readonly="$readonly"
        {{ $attributes }}
    />
</x-admin.form-field>
