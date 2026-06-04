@php
    $kpis = $workspaceData['kpis'];
    $slaDetail = $workspaceData['sla_detail'];
    $messageTimeline = $workspaceData['message_timeline'];
    $lastChannel = $active->last_channel
        ? (\App\Enums\InboxMessageChannel::tryFrom($active->last_channel)?->label() ?? $active->last_channel)
        : __('In-app');
@endphp

<section class="shared-inbox__thread">
    @include('admin.communications.inbox.workspace.thread-header', compact('lastChannel', 'kpis', 'slaDetail'))

    <div class="relative min-h-0 flex-1 overflow-hidden">
        <div
            class="shared-inbox__thread-messages"
            id="inbox-messages"
            x-data
            x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
        >
            @include('admin.communications.inbox.workspace.chat-messages', ['events' => $messageTimeline])
        </div>
    </div>

    @include('admin.communications.inbox.workspace.composer')
</section>
