<x-admin-layout :title="__('Edit customer')" :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => __('Edit')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.crm.customers.update', $customer) }}" data-turbo-frame="_top">@csrf @method('PUT')
            @include('admin.crm.customers.form', ['customer' => $customer])
            <div class="mt-6"><x-primary-button>{{ __('Update') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
