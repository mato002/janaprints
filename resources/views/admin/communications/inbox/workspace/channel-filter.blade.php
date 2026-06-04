<div class="mb-2 flex flex-wrap items-center gap-1">
    <span class="text-[10px] font-semibold uppercase text-slate-500 mr-1">{{ __('Channel') }}</span>
    @php
        $baseQuery = array_merge(request()->query(), ['conversation' => $active->id]);
    @endphp
    <a href="{{ route('admin.communications.inbox.index', collect($baseQuery)->except('channel')->all()) }}"
       data-turbo-frame="erp-main"
       class="rounded-full px-2 py-0.5 text-[10px] {{ empty($channelFilter) ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600' }}">{{ __('All') }}</a>
    @foreach (\App\Enums\InboxMessageChannel::cases() as $ch)
        <a href="{{ route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => $ch->value])) }}"
           data-turbo-frame="erp-main"
           class="rounded-full px-2 py-0.5 text-[10px] {{ ($channelFilter ?? '') === $ch->value ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600' }}">
            {{ $ch->label() }}
        </a>
    @endforeach
    <a href="{{ route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => 'note'])) }}"
       data-turbo-frame="erp-main"
       class="rounded-full px-2 py-0.5 text-[10px] {{ ($channelFilter ?? '') === 'note' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600' }}">{{ __('Notes') }}</a>
    <a href="{{ route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => 'erp'])) }}"
       data-turbo-frame="erp-main"
       class="rounded-full px-2 py-0.5 text-[10px] {{ ($channelFilter ?? '') === 'erp' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600' }}">{{ __('ERP') }}</a>
</div>
