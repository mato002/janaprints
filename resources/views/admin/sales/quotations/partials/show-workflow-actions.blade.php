@if ($quotation->status === App\Enums\QuotationStatus::Draft)
    @can('transition', $quotation)
        <form method="POST" action="{{ route('admin.quotations.submit-approval', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-secondary">{{ __('Submit for approval') }}</button></form>
    @endcan
@endif
@if ($quotation->status === App\Enums\QuotationStatus::PendingApproval)
    @can('approve', $quotation)
        <form method="POST" action="{{ route('admin.quotations.approve', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-primary">{{ __('Approve & send') }}</button></form>
    @endcan
    @can('send', $quotation)
        <form method="POST" action="{{ route('admin.quotations.send', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-secondary">{{ __('Send') }}</button></form>
    @endcan
@endif
@if ($quotation->status === App\Enums\QuotationStatus::Sent)
    @can('transition', $quotation)
        <form method="POST" action="{{ route('admin.quotations.mark-viewed', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-secondary">{{ __('Mark viewed') }}</button></form>
    @endcan
@endif
@if ($quotation->status === App\Enums\QuotationStatus::Viewed)
    @can('transition', $quotation)
        <form method="POST" action="{{ route('admin.quotations.accept', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-primary">{{ __('Accept') }}</button></form>
        <form method="POST" action="{{ route('admin.quotations.reject', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-secondary text-red-600">{{ __('Reject') }}</button></form>
    @endcan
@endif
@if ($quotation->status === App\Enums\QuotationStatus::Accepted)
    @can('convert', $quotation)
        <a
            href="{{ route('admin.sales-orders.create', ['quotation_id' => $quotation->id, 'tab' => 'quotation', 'customer_id' => $quotation->customer_id]) }}"
            class="erp-btn-primary"
            data-turbo-frame="erp-form-modal"
        >{{ __('Convert to sales order') }}</a>
        <form method="POST" action="{{ route('admin.quotations.convert', $quotation) }}" class="inline">@csrf
            <button class="erp-btn-secondary">{{ __('Quick convert') }}</button></form>
    @endcan
@endif
@if ($quotation->salesOrder)
    @can('view', $quotation->salesOrder)
        <a href="{{ route('admin.sales-orders.show', $quotation->salesOrder) }}" class="erp-btn-secondary">{{ __('View sales order') }}</a>
    @endcan
@endif
