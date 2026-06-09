@props([
    'href',
    'label',
])

<x-admin.form-modal-link :href="$href" {{ $attributes }}>
    {{ $label ?? $slot }}
</x-admin.form-modal-link>
