<x-admin-layout :title="__('New print template')" :breadcrumbs="[['label' => __('Print Templates'), 'url' => route('admin.production.print-templates.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New print product template')" />
    <form method="POST" action="{{ route('admin.production.print-templates.store') }}" class="space-y-6">
        @csrf
        @include('admin.production.print-templates.partials.form-fields', ['template' => null])
        <button type="submit" class="erp-btn-primary">{{ __('Create template') }}</button>
    </form>
</x-admin-layout>
