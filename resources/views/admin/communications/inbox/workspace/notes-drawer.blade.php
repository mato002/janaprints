@can('notes', App\Models\Communications\Inbox\CommunicationConversation::class)
    <details class="border-t border-erp-border bg-amber-50/50 lg:hidden">
        <summary class="cursor-pointer px-4 py-2 text-xs font-semibold text-amber-900">{{ __('Internal notes timeline') }}</summary>
        <ul class="max-h-48 space-y-2 overflow-y-auto px-4 pb-3 text-xs">
            @forelse ($active->notes as $note)
                <li class="rounded border border-amber-200 bg-white p-2">
                    <p class="font-semibold text-amber-800">{{ $note->author?->name }} · {{ $note->created_at->format('d M H:i') }}</p>
                    <p class="mt-1 whitespace-pre-wrap text-slate-800">{{ $note->body }}</p>
                    @if (! empty($note->tags))
                        <p class="mt-1">@foreach ($note->tags as $t)<span class="text-amber-700">#{{ $t }}</span> @endforeach</p>
                    @endif
                </li>
            @empty
                <li class="text-slate-500">{{ __('No internal notes yet.') }}</li>
            @endforelse
        </ul>
    </details>
@endcan
