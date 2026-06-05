<x-admin-layout :title="__('Add SMS Provider')">
    <x-admin.page-header :title="__('Add SMS provider')" />
    <form method="POST" action="{{ route('admin.integrations.sms.store') }}" class="space-y-4">
        @csrf
        @include('admin.integrations.sms.form', ['providers' => $providers])
        <button type="submit" class="erp-btn-primary">{{ __('Save configuration') }}</button>
    </form>
</x-admin-layout>
