@props(['logs', 'title' => __('Communication history')])

@can('viewAny', App\Models\Communications\CommunicationLog::class)
    <div class="erp-card mt-6">
        <div class="flex items-center justify-between gap-2">
            <h3 class="erp-card-title">{{ $title }}</h3>
            <x-admin.crm-btn variant="ghost" size="sm" :href="route('admin.communications.logs.timeline')" data-turbo-frame="erp-main">{{ __('View all') }}</x-admin.crm-btn>
        </div>
        <div class="mt-3">
            <x-admin.communication-timeline :logs="$logs" :compact="true" />
        </div>
    </div>
@endcan
