<x-admin-layout :title="__('Create lead')" :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.crm.leads.store') }}">@csrf
            @include('admin.crm.leads.form', ['lead' => null])
            <div class="mt-6"><x-primary-button>{{ __('Create') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
