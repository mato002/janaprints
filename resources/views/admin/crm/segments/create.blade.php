<x-admin-layout :title="__('Create segment')" :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.crm.segments.store') }}">
            @csrf
            @include('admin.crm.segments.partials.form')
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
