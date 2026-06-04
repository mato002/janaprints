@can('notes', App\Models\Communications\Inbox\CommunicationConversation::class)
    <form method="POST" action="{{ route('admin.communications.inbox.notes.store', $active) }}" class="mb-4 space-y-2 rounded-lg border border-amber-200 bg-amber-50/50 p-3">
        @csrf
        @if ($channelFilter)<input type="hidden" name="channel" value="{{ $channelFilter }}">@endif
        <p class="text-[10px] font-semibold uppercase text-amber-800">{{ __('Staff only — customer never sees this') }}</p>
        <textarea name="body" rows="3" class="erp-input w-full text-sm bg-white" placeholder="{{ __('@name mentions · #urgent #artwork tags') }}" required></textarea>
        <input type="text" name="tags" class="erp-input w-full text-xs" placeholder="{{ __('Tags (comma separated)') }}">
        <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm w-full">{{ __('Add note') }}</button>
    </form>
@endcan

<ul class="space-y-2">
    @php
        $sortedNotes = $active->notes->sortByDesc(fn ($n) => in_array('pinned', $n->tags ?? [], true) || in_array('important', $n->tags ?? [], true));
    @endphp
    @forelse ($sortedNotes as $note)
        @php
            $isPinned = ! empty(array_intersect($note->tags ?? [], ['pinned', 'important']));
        @endphp
        <li class="rounded-lg border p-3 text-sm {{ $isPinned ? 'border-amber-400 bg-amber-50' : 'border-erp-border bg-white' }}">
            <p class="flex items-center gap-2 text-[10px] font-semibold text-slate-600">
                @if ($isPinned)<span class="rounded bg-amber-200 px-1 text-amber-900">{{ __('Pinned') }}</span>@endif
                {{ $note->author?->name }} · {{ $note->created_at->format('d M Y H:i') }}
            </p>
            <p class="mt-1 whitespace-pre-wrap text-slate-800">{{ $note->body }}</p>
            @if (! empty($note->tags))
                <p class="mt-1 flex flex-wrap gap-1">
                    @foreach ($note->tags as $tag)<span class="rounded bg-slate-100 px-1.5 text-[10px]">#{{ $tag }}</span>@endforeach
                </p>
            @endif
            @if (! empty($note->mentioned_user_ids))
                <p class="mt-1 text-[10px] text-erp-accent">{{ __('Mentioned') }}: @foreach ($note->mentioned_user_ids as $uid)@php $u = $workspaceData['mentionable_users']->firstWhere('id', $uid); @endphp{{ $u?->name ?? $uid }}@if (! $loop->last), @endif @endforeach</p>
            @endif
        </li>
    @empty
        <li class="text-sm text-slate-500">{{ __('No internal notes yet.') }}</li>
    @endforelse
</ul>
