<div class="border-t border-erp-border bg-white p-3 space-y-2">
    @if ($context && ! empty($context['quick_actions']))
        <div class="flex flex-wrap gap-1 pb-2 border-b border-erp-border/60">
            @foreach ($context['quick_actions'] as $action)
                @if ($action['route'] && (! $action['permission'] || auth()->user()->can($action['permission'])))
                    <a href="{{ route($action['route'], $active->customer_id ? ['customer_id' => $active->customer_id] : []) }}"
                       class="erp-btn erp-btn--secondary erp-btn--xs" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                @elseif (! empty($action['anchor']))
                    <a href="#{{ $action['anchor'] }}" class="erp-btn erp-btn--secondary erp-btn--xs">{{ $action['label'] }}</a>
                @endif
            @endforeach
        </div>
    @endif

    @can('reply', App\Models\Communications\Inbox\CommunicationConversation::class)
        <form method="POST" action="{{ route('admin.communications.inbox.reply', $active) }}" class="flex gap-2">
            @csrf
            <select name="channel" class="erp-input w-28 text-xs shrink-0">
                @foreach (\App\Enums\InboxMessageChannel::cases() as $ch)
                    <option value="{{ $ch->value }}">{{ $ch->label() }}</option>
                @endforeach
            </select>
            <textarea name="body" rows="2" class="erp-input flex-1 text-sm" placeholder="{{ __('Reply…') }}" required></textarea>
            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm shrink-0">{{ __('Send') }}</button>
        </form>
    @endcan

    @can('notes', App\Models\Communications\Inbox\CommunicationConversation::class)
        <form method="POST" action="{{ route('admin.communications.inbox.notes.store', $active) }}" class="flex gap-2" id="inbox-note">
            @csrf
            <textarea name="body" rows="2" class="erp-input flex-1 text-sm bg-amber-50"
                      placeholder="{{ __('Internal note — @name mentions, #tags') }}" required></textarea>
            <input type="text" name="tags" class="erp-input w-24 text-xs shrink-0" placeholder="{{ __('Tags') }}">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm shrink-0">{{ __('Note') }}</button>
        </form>
    @endcan
</div>
