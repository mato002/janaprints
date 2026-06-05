<x-admin-layout :title="__('Log Complaint')" :breadcrumbs="[['label' => __('Complaints'), 'url' => route('admin.commercial.complaints.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Log complaint')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.complaints.store') }}" class="space-y-4 p-4">
            @csrf
            @include('admin.commercial.complaints.form')
            <button type="submit" class="erp-btn-primary">{{ __('Save complaint') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
