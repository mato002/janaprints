@include('documents.partials.header', ['document' => $document])

@include('documents.partials.parties', [
    'customer' => $document['customer'],
    'customerLabel' => $document['customerLabel'] ?? __('Bill To'),
    'dates' => $document['dates'] ?? [],
])

@include('documents.partials.commercial-meta', ['meta' => $document['meta'] ?? []])

@include('documents.partials.items-table', [
    'columns' => $document['columns'],
    'items' => $document['items'],
])

@include('documents.partials.totals-notes-row', [
    'notesTerms' => $document['notesTerms'] ?? [],
    'totals' => $document['totals'],
])

@include('documents.partials.payment-footer', ['paymentFooter' => $document['paymentFooter'] ?? []])
@include('documents.partials.footer')
