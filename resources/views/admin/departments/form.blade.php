<x-admin-layout :title="$department ? __('Edit department') : __('Create department')" :breadcrumbs="[['label' => __('Departments'), 'url' => route('admin.departments.index')], ['label' => $department ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @include('admin.departments.partials.form-fields', ['department' => $department ?? null, 'companies' => $companies])
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
