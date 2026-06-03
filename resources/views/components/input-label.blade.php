@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-700'.($required ? ' required' : '')]) }}>
    {{ $value ?? $slot }}
</label>
