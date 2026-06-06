<section class="qr-360__card">
    <h2 class="qr-360__card-title">{{ __('Internal Collaboration') }}</h2>

    @can('update', $quoteRequest)
        <form method="POST" action="{{ route('admin.public-quote-requests.notes.store', $quoteRequest) }}" class="mb-4">
            @csrf
            <label class="qr-360__label" for="qr-note-body">{{ __('Add Note') }}</label>
            <textarea
                id="qr-note-body"
                name="body"
                class="erp-input mt-1 w-full min-h-[4.5rem] text-sm"
                rows="3"
                placeholder="{{ __('Add an internal note for the commercial team…') }}"
                required
            >{{ old('body') }}</textarea>
            <div class="mt-2">
                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">{{ __('Save Note') }}</button>
            </div>
        </form>
    @endcan

    <ul class="crm-360__notes-feed" role="list">
        @forelse ($workspace['notes_feed'] as $note)
            <li class="crm-360__note-card">
                <div class="crm-360__note-head">
                    <span class="crm-360__note-author">{{ $note['author'] }}</span>
                    <time class="crm-360__note-time">{{ $note['at']?->format('M j, Y g:i A') }} · {{ $note['at']?->diffForHumans() }}</time>
                </div>
                <p class="crm-360__note-body whitespace-pre-wrap">{{ $note['body'] }}</p>
                @if ($note['legacy'])
                    <p class="mt-2 text-[11px] text-slate-500">{{ __('Imported from legacy notes field') }}</p>
                @endif
            </li>
        @empty
            <li class="crm-360__empty-inline">{{ __('No internal notes yet') }}</li>
        @endforelse
    </ul>
</section>
