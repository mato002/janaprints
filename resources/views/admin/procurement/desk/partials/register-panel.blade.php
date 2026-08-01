@php
    use App\Support\Procurement\ProcurementDeskViews;
@endphp

@switch ($activeProcurementView ?? ProcurementDeskViews::DESK)
    @case(ProcurementDeskViews::REQUESTS)
        @include('admin.procurement.requests.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(ProcurementDeskViews::SUPPLIERS)
        @include('admin.procurement.vendors.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(ProcurementDeskViews::RFQS)
        @include('admin.procurement.rfqs.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(ProcurementDeskViews::ORDERS)
        @include('admin.procurement.orders.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(ProcurementDeskViews::RECEIPTS)
        @include('admin.procurement.receipts.partials.register-content', ['embeddedInDesk' => true])
        @break
    @case(ProcurementDeskViews::APPROVALS)
        @include('admin.procurement.approvals.partials.register-content', ['embeddedInDesk' => true])
        @break
@endswitch
