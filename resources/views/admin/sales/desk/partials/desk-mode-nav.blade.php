@php
    use App\Support\Sales\SalesDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = SalesDeskViews::normalize($activeSalesView ?? request('view', SalesDeskViews::DESK));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => SalesDeskViews::DESK,
            'label' => __('Walk-in'),
            'url' => SalesDeskViews::deskUrl(SalesDeskViews::DESK),
            'visible' => ($user?->can('crm.customers.create') || $user?->can('sales_orders.create')) ?? false,
        ],
        [
            'key' => SalesDeskViews::QUOTES,
            'label' => __('Quotes'),
            'url' => SalesDeskViews::quotesUrl(),
            'visible' => $user?->can('quotations.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::ORDERS,
            'label' => __('Orders'),
            'url' => SalesDeskViews::ordersUrl(),
            'visible' => $user?->can('sales_orders.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::ARTWORK,
            'label' => __('Artwork'),
            'url' => SalesDeskViews::artworkUrl(),
            'visible' => $user?->can('artwork.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::APPROVALS,
            'label' => __('Approvals'),
            'url' => SalesDeskViews::approvalsUrl(),
            'visible' => $user?->can('commercial.approvals.view') ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <div class="sales-desk-ribbon mb-3 shrink-0">
        <nav class="sales-desk-ribbon__tabs" aria-label="{{ __('Sales desk modes') }}">
            @foreach ($modes as $mode)
                <a
                    href="{{ WorkspaceEmbed::url($mode['url']) }}"
                    @class([
                        'sales-desk-ribbon__tab',
                        'sales-desk-ribbon__tab--'.$mode['key'] => filled($mode['key'] ?? null),
                        'sales-desk-ribbon__tab--active' => $mode['key'] === $active,
                    ])
                    data-turbo-frame="{{ $frame }}"
                    data-turbo-action="advance"
                >{{ $mode['label'] }}</a>
            @endforeach
        </nav>
    </div>
@endif
