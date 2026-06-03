<x-admin-layout :title="__('New artwork request')" :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New artwork request')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.artwork.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @include('admin.artwork.requests.partials.form')
            <button type="submit" class="erp-btn-primary">{{ __('Create request') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
