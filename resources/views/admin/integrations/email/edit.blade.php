<x-admin-layout :title="__('Edit Email Provider')" :breadcrumbs="[['label' => __('Integrations')], ['label' => __('Email Settings')], ['label' => __('Edit')]]">
    <x-admin.page-header :title="__('Edit email provider')" :description="$setting->provider->label()" />
    <form method="POST" action="{{ route('admin.integrations.email.update', $setting) }}" class="space-y-4">
        @csrf @method('PUT')
        @include('admin.integrations.email.form', ['setting' => $setting, 'providers' => $providers])
        <div class="flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save configuration') }}</button>
            <a href="{{ route('admin.integrations.email.show', $setting) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
