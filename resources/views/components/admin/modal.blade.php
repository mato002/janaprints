@props([
    'title',
    'breadcrumbs' => [],
    'maxWidth' => '2xl',
])

<x-admin.modal-form :title="$title" :breadcrumbs="$breadcrumbs" :maxWidth="$maxWidth" {{ $attributes }}>
    {{ $slot }}
</x-admin.modal-form>
