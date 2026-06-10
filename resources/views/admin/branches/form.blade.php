<x-admin-layout :title="$branch ? __('Edit branch') : __('Create branch')" :breadcrumbs="[['label' => __('Branches'), 'url' => route('admin.branches.index')], ['label' => $branch ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @include('admin.branches.partials.form-fields', ['branch' => $branch ?? null, 'companies' => $companies])
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
