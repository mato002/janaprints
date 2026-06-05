<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold">{{ __('Linked Documents') }}</h3>
    <ul class="space-y-2 text-sm">
        @forelse ($tabData['procurement_documents'] as $doc)
            <li>{{ $doc->document_label ?? class_basename($doc->document_type) }} (#{{ $doc->document_id }})</li>
        @empty
            <li class="text-slate-500">{{ __('No linked procurement documents.') }}</li>
        @endforelse
        @foreach ($tabData['handovers'] as $h)
            <li>{{ __('Handover') }} {{ $h->handover_no }}</li>
        @endforeach
        @foreach ($tabData['transfers'] as $t)
            <li>{{ __('Transfer') }} {{ $t->transfer_no }}</li>
        @endforeach
    </ul>
</x-admin.card>
