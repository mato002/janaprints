<x-admin-layout :title="$title" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="$title" :description="$description" />
    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.empty-state', ['title' => $emptyTitle, 'message' => $emptyMessage])
</x-admin-layout>
