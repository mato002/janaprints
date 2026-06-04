<div class="shrink-0 border-b border-erp-border bg-white/95 px-3 py-2 text-[10px] text-slate-600">
    <div class="grid grid-cols-3 gap-x-2 gap-y-1 sm:grid-cols-5 lg:grid-cols-9">
        <div><span class="font-semibold text-slate-500">{{ __('Messages') }}</span><br>{{ $kpis['total_messages'] }}</div>
        <div><span class="font-semibold text-slate-500">{{ __('Age') }}</span><br>{{ $kpis['conversation_age_label'] }}</div>
        <div><span class="font-semibold text-slate-500">{{ __('1st reply') }}</span><br>{{ $kpis['first_response_minutes'] ?? '—' }}m</div>
        <div><span class="font-semibold text-slate-500">{{ __('Last reply') }}</span><br>{{ $kpis['last_response_minutes'] ?? '—' }}m</div>
        <div><span class="font-semibold text-slate-500">{{ __('Assigned') }}</span><br class="truncate">{{ $kpis['assigned_user'] }}</div>
        <div><span class="font-semibold text-slate-500">{{ __('Unread') }}</span><br>{{ $kpis['unread_count'] }}</div>
        <div><span class="font-semibold text-slate-500">{{ __('SLA 1st') }}</span><br><span class="{{ $slaDetail['first_response']->badgeClass() }} rounded px-1">{{ $slaDetail['first_response']->label() }}</span></div>
        <div><span class="font-semibold text-slate-500">{{ __('SLA follow') }}</span><br><span class="{{ $slaDetail['follow_up']->badgeClass() }} rounded px-1">{{ $slaDetail['follow_up']->label() }}</span></div>
        <div><span class="font-semibold text-slate-500">{{ __('SLA resolve') }}</span><br><span class="{{ $slaDetail['resolution']->badgeClass() }} rounded px-1">{{ $slaDetail['resolution']->label() }}</span></div>
    </div>
    <details class="mt-1 lg:hidden">
        <summary class="cursor-pointer text-erp-accent">{{ __('Assignment & watchers') }}</summary>
        <div class="mt-1">@include('admin.communications.inbox.partials.assignment-bar')</div>
    </details>
    <div class="mt-1 hidden lg:block">@include('admin.communications.inbox.partials.assignment-bar')</div>
</div>
