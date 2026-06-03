<x-admin-layout :title="__('Edit artwork request')" :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => $request->request_number, 'url' => route('admin.artwork.show', $request)], ['label' => __('Edit')]]">
    <x-admin.page-header :title="__('Edit :number', ['number' => $request->request_number])" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.artwork.update', $request) }}" class="space-y-4 max-w-xl">
            @csrf
            @method('PUT')
            @include('admin.artwork.requests.partials.form', ['request' => $request])
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
