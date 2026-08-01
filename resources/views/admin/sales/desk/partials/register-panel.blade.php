@php
    use App\Support\Sales\SalesDeskViews;
@endphp

@switch ($activeSalesView ?? SalesDeskViews::DESK)
    @case(SalesDeskViews::QUOTES)
        @include('admin.sales.quotations.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(SalesDeskViews::ORDERS)
        @include('admin.sales.orders.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(SalesDeskViews::ARTWORK)
        @include('admin.artwork.requests.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(SalesDeskViews::APPROVALS)
        @include('admin.commercial.approvals.partials.register-content', ['embeddedInDesk' => true])
        @break
@endswitch
