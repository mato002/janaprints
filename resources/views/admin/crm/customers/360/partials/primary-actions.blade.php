@props([
    'customer',
    'latestOrderForRepeat' => null,
])

@php
    $canStartConversation = auth()->user()->can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class);
    $canCreateQuote = auth()->user()->can('quotations.create');
    $canCreateOrder = auth()->user()->can('sales_orders.create');
@endphp

<div class="crm-360__action-bar" role="toolbar" aria-label="{{ __('Customer actions') }}">
    @if ($canStartConversation)
        <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" class="crm-360__action-primary" data-turbo-frame="erp-main">
            @csrf
            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">
                {{ __('Start conversation') }}
            </button>
        </form>
    @endif

    @if ($canCreateQuote)
        <a
            href="{{ route('admin.quotations.create', ['customer_id' => $customer->id]) }}"
            class="crm-360__btn crm-360__btn--outline crm-360__btn--sm crm-360__action-secondary"
            data-turbo-frame="erp-form-modal"
        >{{ __('Create quote') }}</a>
    @endif

    @if ($canCreateOrder)
        <a
            href="{{ route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct']) }}"
            class="crm-360__btn crm-360__btn--outline crm-360__btn--sm crm-360__action-secondary"
            data-turbo-frame="erp-form-modal"
        >{{ __('New order') }}</a>
    @endif

    @include('admin.crm.customers.360.partials.customer-actions-dropdown', [
        'customer' => $customer,
        'latestOrderForRepeat' => $latestOrderForRepeat,
        'buttonClass' => 'crm-360__btn crm-360__btn--ghost crm-360__btn--sm',
        'buttonLabel' => __('More'),
        'omitPrimary' => true,
    ])
</div>
