<x-admin-layout :title="__('Edit print template')" :breadcrumbs="[['label' => __('Print Templates'), 'url' => route('admin.production.print-templates.index')], ['label' => $template->name]]">
    <x-admin.page-header :title="$template->name" :description="$template->code" />
    <form method="POST" action="{{ route('admin.production.print-templates.update', $template) }}" class="space-y-6">
        @csrf @method('PUT')
        @include('admin.production.print-templates.partials.form-fields')
        <button type="submit" class="erp-btn-primary">{{ __('Save template') }}</button>
    </form>
</x-admin-layout>
