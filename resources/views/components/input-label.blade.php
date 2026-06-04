@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-erp-primary'.($required ? ' required' : '')]) }}>
    {{ $value ?? $slot }}
</label>
