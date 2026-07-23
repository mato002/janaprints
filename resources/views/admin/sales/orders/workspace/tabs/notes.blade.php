<article class="so-360__card">
    <h2 class="so-360__card-title">{{ __('Notes') }}</h2>

    @forelse ($salesOrder->orderNotes as $note)
        <div class="border-b border-slate-100 py-2 text-sm last:border-0">
            <p class="font-medium text-slate-800">{{ $note->user?->name ?? __('System') }}</p>
            <p class="text-slate-600">{{ $note->note }}</p>
            @if ($note->created_at)
                <p class="mt-0.5 text-xs text-slate-400">{{ $note->created_at->format('M j, Y H:i') }}</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">{{ __('No notes yet.') }}</p>
    @endforelse

    @can('view', $salesOrder)
        <form method="POST" action="{{ route('admin.sales-orders.notes.store', $salesOrder) }}" class="mt-4 space-y-2">
            @csrf
            <textarea name="note" class="erp-input w-full" rows="3" required placeholder="{{ __('Add an internal note…') }}"></textarea>
            <button class="erp-btn-secondary">{{ __('Add note') }}</button>
        </form>
    @endcan
</article>
