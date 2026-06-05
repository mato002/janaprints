<x-admin-layout :title="__('Edit SMS Provider')">
    <x-admin.page-header :title="__('Edit SMS provider')" :description="$setting->provider->label()" />
    <form method="POST" action="{{ route('admin.integrations.sms.update', $setting) }}" class="space-y-4">
        @csrf @method('PUT')
        @include('admin.integrations.sms.form', ['setting' => $setting, 'providers' => $providers])
        <button type="submit" class="erp-btn-primary">{{ __('Save configuration') }}</button>
    </form>
</x-admin-layout>
