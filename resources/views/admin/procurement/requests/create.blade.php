<x-admin-layout :title="__('Create Purchase Request')" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Purchase Requests'), 'url' => route('admin.procurement.requests.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Create purchase request')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.requests.store') }}" class="space-y-6">
            @csrf
            @include('admin.procurement.requests.partials.header-form')
            @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'request'])
            <x-primary-button>{{ __('Save request') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
