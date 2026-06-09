<x-admin-layout :title="__('Edit Program')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => $program->title]]">
    <x-admin.page-header :title="__('Edit Training Program')" />

    <form method="POST" action="{{ route('admin.hr.training.programs.update', $program) }}" class="erp-card max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.hr.training.programs.partials.form', ['program' => $program, 'types' => $types])
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
            <a href="{{ route('admin.hr.training.programs.show', $program) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
