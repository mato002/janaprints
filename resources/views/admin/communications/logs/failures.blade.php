<x-admin-layout :title="__('Failed communications')" :breadcrumbs="[['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')], ['label' => __('Failures')]]">
    @include('admin.communications.logs.partials.nav')
    <x-admin.page-header :title="__('Failures')" />
    <div class="erp-card">
        <x-admin.communication-timeline :logs="$logs" />
        @if ($logs->hasPages())<div class="mt-4">{{ $logs->links() }}</div>@endif
    </div>
</x-admin-layout>
