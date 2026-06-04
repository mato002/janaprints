<p class="mb-3 text-xs text-slate-500">{{ __('Full activity: messages, ERP events, system changes.') }}</p>
@include('admin.communications.inbox.workspace.channel-filter')
<div class="space-y-2 max-h-[calc(100vh-16rem)] overflow-y-auto">
    @include('admin.communications.inbox.workspace.timeline-feed', ['events' => $events])
</div>
