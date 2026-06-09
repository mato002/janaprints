@props([
    'title',
    'breadcrumbs' => [],
    'maxWidth' => '2xl',
])

<x-admin.form-page :title="$title" :breadcrumbs="$breadcrumbs" :maxWidth="$maxWidth" {{ $attributes }}>
    {{ $slot }}
</x-admin.form-page>
