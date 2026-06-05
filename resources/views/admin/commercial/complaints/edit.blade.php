<x-admin-layout :title="__('Edit Complaint')" :breadcrumbs="[['label' => __('Complaints'), 'url' => route('admin.commercial.complaints.index')], ['label' => $complaint->subject]]">
    <x-admin.page-header :title="__('Edit complaint')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.complaints.update', $complaint) }}" class="space-y-4 p-4">
            @csrf @method('PUT')
            @include('admin.commercial.complaints.form', ['complaint' => $complaint])
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
