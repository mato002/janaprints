@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Procurement\ProcurementDeskViews;

    // Inside the module workspace shell, modes live on Layer 2 secondary tabs.
    // Keep this strip only for standalone (non-embedded) procurement pages.
    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = ProcurementDeskViews::normalize($activeProcurementView ?? request('view', ProcurementDeskViews::REQUESTS));
    $user = auth()->user();
    $modes = collect([
        [
            'key' => ProcurementDeskViews::REQUESTS,
            'label' => __('Requests'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::REQUESTS),
            'visible' => $user?->can('procurement.requests.view') ?? false,
        ],
        [
            'key' => ProcurementDeskViews::SUPPLIERS,
            'label' => __('Suppliers'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::SUPPLIERS),
            'visible' => $user?->can('procurement.vendors.view') ?? false,
        ],
        [
            'key' => ProcurementDeskViews::RFQS,
            'label' => __('RFQs'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
            'visible' => ($user?->can('procurement.rfq.view') || $user?->can('procurement.vendors.view')) ?? false,
        ],
        [
            'key' => ProcurementDeskViews::ORDERS,
            'label' => __('Orders'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::ORDERS),
            'visible' => ($user?->can('procurement.orders.view') || $user?->can('procurement.vendors.view')) ?? false,
        ],
        [
            'key' => ProcurementDeskViews::RECEIPTS,
            'label' => __('Receipts'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RECEIPTS),
            'visible' => ($user?->can('procurement.orders.view') || $user?->can('procurement.vendors.view')) ?? false,
        ],
        [
            'key' => ProcurementDeskViews::APPROVALS,
            'label' => __('Approvals'),
            'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::APPROVALS),
            'visible' => $user?->can('procurement.approvals.view') ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="workspace-context-tabs" aria-label="{{ __('Procurement desk modes') }}">
        @foreach ($modes as $mode)
            <a
                href="{{ $mode['url'] }}"
                @class([
                    'workspace-context-tab',
                    'workspace-context-tab--active' => $mode['key'] === $active,
                ])
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >{{ $mode['label'] }}</a>
        @endforeach
    </nav>
@endif
