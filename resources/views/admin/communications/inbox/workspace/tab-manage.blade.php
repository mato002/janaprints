<p class="mb-3 text-[11px] text-slate-500">{{ __('Assignment, SLA, and tags — kept here so the chat stays message-first.') }}</p>

<div class="grid grid-cols-2 gap-2 rounded-lg border border-erp-border bg-white p-2 text-[11px] text-slate-600">
    <div><span class="text-slate-500">{{ __('Messages') }}</span><br><span class="font-medium">{{ $kpis['total_messages'] }}</span></div>
    <div><span class="text-slate-500">{{ __('Age') }}</span><br><span class="font-medium">{{ $kpis['conversation_age_label'] }}</span></div>
    <div><span class="text-slate-500">{{ __('1st response') }}</span><br>{{ $kpis['first_response_minutes'] ?? '—' }}m</div>
    <div><span class="text-slate-500">{{ __('Last response') }}</span><br>{{ $kpis['last_response_minutes'] ?? '—' }}m</div>
    <div><span class="text-slate-500">{{ __('SLA 1st') }}</span><br><span class="{{ $slaDetail['first_response']->badgeClass() }} rounded px-1">{{ $slaDetail['first_response']->label() }}</span></div>
    <div><span class="text-slate-500">{{ __('SLA follow') }}</span><br><span class="{{ $slaDetail['follow_up']->badgeClass() }} rounded px-1">{{ $slaDetail['follow_up']->label() }}</span></div>
    <div><span class="text-slate-500">{{ __('SLA resolve') }}</span><br><span class="{{ $slaDetail['resolution']->badgeClass() }} rounded px-1">{{ $slaDetail['resolution']->label() }}</span></div>
    <div><span class="text-slate-500">{{ __('SLA') }}</span><br><span class="{{ $kpis['sla_status']->badgeClass() }} rounded px-1 font-semibold">{{ $kpis['sla_status']->label() }}</span></div>
</div>

<div class="mt-3 rounded-lg border border-erp-border bg-white p-2">
    @include('admin.communications.inbox.partials.assignment-bar')
    @can('assign', App\Models\Communications\Inbox\CommunicationConversation::class)
        <form method="POST" action="{{ route('admin.communications.inbox.assign', $active) }}" class="mt-2 flex gap-1" data-turbo-frame="{{ $inboxTurboFrame }}">
            @csrf
            <input type="hidden" name="action" value="add_watcher">
            <select name="watcher_user_id" class="erp-input flex-1 text-xs">
                <option value="">{{ __('Add watcher…') }}</option>
                @foreach ($staff as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Add') }}</button>
        </form>
    @endcan
</div>

<div class="mt-3">
    @include('admin.communications.inbox.workspace.tags-panel')
</div>
