<x-admin-layout :title="__('Create customer')" :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.crm.customers.store') }}">@csrf
            @include('admin.crm.customers.form', ['customer' => null])
            <div class="mt-6"><x-primary-button>{{ __('Create') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
