@props([
    'action',
    'method' => 'POST',
])

<x-admin.form-shell :action="$action" :method="$method" {{ $attributes }}>
    {{ $slot }}
</x-admin.form-shell>
