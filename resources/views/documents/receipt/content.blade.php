@include('documents.partials.header', ['document' => $document])

@include('documents.partials.parties', [
    'customer' => $document['customer'],
    'customerLabel' => $document['customerLabel'] ?? __('Received From'),
    'dates' => $document['dates'] ?? [],
])

@include('documents.partials.commercial-meta', ['meta' => $document['meta'] ?? [], 'stacked' => true])

@include('documents.partials.allocations-table', ['allocations' => $document['allocations'] ?? []])

@include('documents.partials.totals-notes-row', [
    'notesTerms' => $document['notesTerms'] ?? [],
    'totals' => $document['totals'],
])

@include('documents.partials.footer')
