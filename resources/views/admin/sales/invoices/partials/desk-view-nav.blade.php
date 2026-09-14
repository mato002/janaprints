@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Sales\ReceivablesInvoiceViews;

    $active = ReceivablesInvoiceViews::normalize($activeView ?? request('view'));
    $frame = WorkspaceEmbed::turboFrame();
    $query = array_filter([
        'embedded' => request()->boolean('embedded') ? '1' : null,
        'customer' => $customerQuery ?? null,
        'customer_id' => $customer?->id ?? null,
    ]);
    $modes = [
        [
            'key' => ReceivablesInvoiceViews::JOBS,
            'label' => __('Jobs'),
            'url' => ReceivablesInvoiceViews::jobsUrl($query),
        ],
        [
            'key' => ReceivablesInvoiceViews::INVOICES,
            'label' => __('Invoices'),
            'url' => ReceivablesInvoiceViews::invoicesUrl($query),
        ],
    ];
@endphp

<div class="sales-desk-ribbon mb-3 shrink-0">
    <nav class="sales-desk-ribbon__tabs" aria-label="{{ __('Receivables invoices desk') }}">
        @foreach ($modes as $mode)
            <a
                href="{{ WorkspaceEmbed::url($mode['url']) }}"
                @class([
                    'sales-desk-ribbon__tab',
                    'sales-desk-ribbon__tab--'.$mode['key'],
                    'sales-desk-ribbon__tab--active' => $mode['key'] === $active,
                ])
                data-turbo-frame="{{ $frame }}"
                data-turbo-action="advance"
            >{{ $mode['label'] }}</a>
        @endforeach
    </nav>
</div>
