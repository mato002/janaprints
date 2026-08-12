<x-admin.card class="mt-6">
    <h2 class="mb-4 font-medium text-slate-900">{{ __('Notes') }}</h2>

    @if ($quotation->quotationNotes->isNotEmpty())
        <div class="mb-4 space-y-3">
            @foreach ($quotation->quotationNotes as $note)
                <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                    <p class="text-sm text-slate-800">{{ $note->note }}</p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $note->user?->name }}
                        · {{ $note->created_at?->format('M j, Y, g:i A') }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.quotations.notes.store', $quotation) }}">@csrf
        <textarea name="note" class="erp-input w-full" rows="3" placeholder="{{ __('Add a note…') }}" required></textarea>
        <div class="mt-3">
            <button class="erp-btn-secondary text-sm">{{ __('Add note') }}</button>
        </div>
    </form>
</x-admin.card>
