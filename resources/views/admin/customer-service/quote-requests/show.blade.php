<x-admin-layout
    :title="__('Quote Request').' '.$workspace['reference']"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Quote Requests'), 'url' => route('admin.public-quote-requests.index')],
        ['label' => $workspace['reference']],
    ]"
>
    <div
        class="qr-360"
        x-data="{
            artworkOpen: false,
            activeArtwork: @js($workspace['artwork_files'][0]['id'] ?? 'primary'),
            timelineOpen: true,
        }"
    >
        @include('admin.customer-service.quote-requests.workspace.header')

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                @include('admin.customer-service.quote-requests.workspace.next-action')
                @include('admin.customer-service.quote-requests.workspace.snapshot')
                @include('admin.customer-service.quote-requests.workspace.artwork')
                @include('admin.customer-service.quote-requests.workspace.action-bar')
                @include('admin.customer-service.quote-requests.workspace.sales-review')
                @include('admin.customer-service.quote-requests.workspace.timeline')
                @include('admin.customer-service.quote-requests.workspace.collaboration')
                @include('admin.customer-service.quote-requests.workspace.conversion-tracker')
            </div>

            <div class="xl:col-span-4">
                @include('admin.customer-service.quote-requests.workspace.sidebar')
            </div>
        </div>

        @include('admin.customer-service.quote-requests.workspace.artwork-modal')
    </div>
</x-admin-layout>
