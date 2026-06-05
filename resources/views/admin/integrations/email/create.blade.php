<x-admin-layout :title="__('Add Email Provider')" :breadcrumbs="[['label' => __('Integrations')], ['label' => __('Email Settings')], ['label' => __('Add')]]">
    <x-admin.page-header :title="__('Add email provider')" />
    <form method="POST" action="{{ route('admin.integrations.email.store') }}" class="space-y-4">
        @csrf
        @include('admin.integrations.email.form', ['providers' => $providers])
        <div class="flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save configuration') }}</button>
            <a href="{{ route('admin.integrations.email.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
