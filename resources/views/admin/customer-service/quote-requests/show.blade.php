<x-admin-layout
    :title="__('Quote Request').' '.$workspace['reference']"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Quote Requests'), 'url' => route('admin.public-quote-requests.index')],
        ['label' => $workspace['reference']],
    ]"
>
    <div class="crm-360 qr-intake" x-data="{ artworkOpen: false }">
        @include('admin.customer-service.quote-requests.workspace.header')
        @include('admin.customer-service.quote-requests.workspace.customer-card')

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                @include('admin.customer-service.quote-requests.workspace.artwork')
                @include('admin.customer-service.quote-requests.workspace.summary')
                @include('admin.customer-service.quote-requests.workspace.timeline')
                @include('admin.customer-service.quote-requests.workspace.commercial-review')
                @include('admin.customer-service.quote-requests.workspace.notes')
            </div>

            <div class="xl:col-span-4">
                @include('admin.customer-service.quote-requests.workspace.sidebar')
            </div>
        </div>

        @include('admin.customer-service.quote-requests.workspace.artwork-modal')
    </div>
</x-admin-layout>
