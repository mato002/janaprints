<div class="crm-360__tab-stack">
    @can('update', $customer)
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title">{{ __('Add note') }}</h2>
            <form method="POST" action="{{ route('admin.crm.customers.notes.store', $customer) }}">
                @csrf
                <textarea name="note" class="erp-input w-full min-h-[4.5rem] text-sm" rows="3" placeholder="{{ __('Internal note about this customer…') }}" required></textarea>
                <div class="mt-3">
                    <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Add note') }}</x-admin.crm-btn>
                </div>
            </form>
        </section>
    @endcan

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Notes feed') }}</h2>
        <ul class="crm-360__notes-feed" role="list">
            @forelse ($customer->customerNotes->sortByDesc('created_at') as $note)
                <li class="crm-360__note-card">
                    <div class="crm-360__note-head">
                        <span class="crm-360__note-author">{{ $note->user?->name ?? __('Unknown') }}</span>
                        <time class="crm-360__note-time">{{ $note->created_at?->diffForHumans() }}</time>
                    </div>
                    <p class="crm-360__note-body">{{ $note->note }}</p>
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No notes yet') }}</li>
            @endforelse
        </ul>
    </section>
</div>
