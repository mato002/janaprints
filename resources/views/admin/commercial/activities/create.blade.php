<x-admin-layout :title="__('Log activity')" :breadcrumbs="[['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')], ['label' => __('Create')]]">
    <x-admin.card class="max-w-4xl">
        <form method="POST" action="{{ route('admin.commercial.activities.store') }}">
            @csrf
            @include('admin.commercial.activities.partials.form')
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <a href="{{ route('admin.commercial.activities.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
