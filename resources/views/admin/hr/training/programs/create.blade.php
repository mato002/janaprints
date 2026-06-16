<x-admin-layout :title="__('New Program')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('New Program')]]">
    <x-admin.page-header :title="__('New Training Program')" />

    <form method="POST" action="{{ route('admin.hr.training.programs.store') }}" class="erp-card max-w-3xl">
        @csrf
        @include('admin.hr.training.programs.partials.form', ['types' => $types, 'statuses' => $statuses])
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Create program') }}</button>
            <a href="{{ route('admin.hr.training.programs.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
