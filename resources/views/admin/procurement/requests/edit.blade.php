<x-admin-layout :title="__('Edit Purchase Request')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Purchase Requests'), 'url' => route('admin.procurement.requests.index')], ['label' => $purchaseRequest->request_number]]">
    <x-admin.page-header :title="__('Edit purchase request')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.requests.update', $purchaseRequest) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('admin.procurement.requests.partials.header-form', ['purchaseRequest' => $purchaseRequest])
            @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'request', 'existing' => $purchaseRequest->items])
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
