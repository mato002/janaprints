<div class="rounded border border-erp-border/60 bg-white p-2">
    <h4 class="text-xs font-semibold uppercase text-slate-600">{{ __('Conversation tags') }}</h4>
    <form method="POST" action="{{ route('admin.communications.inbox.tags.update', $active) }}" class="mt-2 space-y-2" data-turbo-frame="{{ $inboxTurboFrame }}">
        @csrf
        @if ($channelFilter)<input type="hidden" name="channel" value="{{ $channelFilter }}">@endif
        <input type="text" name="tags" value="{{ implode(', ', $active->tags ?? []) }}" class="erp-input w-full text-xs" placeholder="{{ __('urgent, vip, artwork') }}">
        <div class="flex flex-wrap gap-1">
            @foreach ($workspaceData['suggested_tags'] ?? [] as $tag)
                <button type="button" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600 hover:bg-erp-accent/10"
                        onclick="const i=this.closest('form').querySelector('[name=tags]'); i.value = i.value ? i.value+', {{ $tag }}' : '{{ $tag }}'">#{{ $tag }}</button>
            @endforeach
        </div>
        <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs w-full">{{ __('Save tags') }}</button>
    </form>
</div>
