<x-admin-layout :title="__('New Requisition')">
    <x-admin.page-header :title="__('New Job Requisition')" />

    <form method="POST" action="{{ route('admin.hr.recruitment.requisitions.store') }}" class="erp-card max-w-3xl">
        @csrf
        @include('admin.hr.recruitment.partials.requisition-form', ['formData' => $formData])
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Create requisition') }}</button>
            <a href="{{ route('admin.hr.recruitment.requisitions.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
