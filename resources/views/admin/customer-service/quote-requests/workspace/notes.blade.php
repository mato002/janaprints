<div class="crm-360__tab-stack">
    @can('update', $quoteRequest)
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title">{{ __('Add Note') }}</h2>
            <form method="POST" action="{{ route('admin.public-quote-requests.notes.store', $quoteRequest) }}">
                @csrf
                <textarea name="body" class="erp-input w-full min-h-[5rem] text-sm" rows="4" placeholder="{{ __('Add an internal note for the commercial team…') }}" required>{{ old('body') }}</textarea>
                <div class="mt-3">
                    <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">{{ __('Save Note') }}</button>
                </div>
            </form>
        </section>
    @endcan

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Internal Notes') }}</h2>
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
</div>
