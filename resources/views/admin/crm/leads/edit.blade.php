<x-admin-layout :title="__('Edit lead')" :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => __('Edit')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.crm.leads.update', $lead) }}" data-turbo-frame="_top">@csrf @method('PUT')
            @include('admin.crm.leads.form', ['lead' => $lead])
            <div class="mt-6"><x-primary-button>{{ __('Update') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
