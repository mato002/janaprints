@props([
    'customer',
    'latestOrderForRepeat' => null,
    'closeOnClick' => true,
])

@php
    $close = $closeOnClick ? '@click="open = false"' : '';
@endphp

@can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
    <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" class="block" data-turbo-frame="erp-main">
        @csrf
        <button type="submit" class="crm-360__more-item w-full text-left" role="menuitem" {!! $close !!}>
            {{ __('Start conversation') }}
        </button>
    </form>
@endcan

@can('quotations.create')
    <a
        href="{{ route('admin.quotations.create', ['customer_id' => $customer->id]) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-form-modal"
        {!! $close !!}
    >{{ __('Create Quotation') }}</a>
@endcan

@can('sales_orders.create')
    <a
        href="{{ route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct']) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-form-modal"
        {!! $close !!}
    >{{ __('Create Direct Order') }}</a>
    <a
        href="{{ route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'quotation']) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-form-modal"
        {!! $close !!}
    >{{ __('Create from Quotation') }}</a>
    @if (! empty($latestOrderForRepeat))
        <form
            method="POST"
            action="{{ route('admin.crm.customers.repeat-order', [$customer, $latestOrderForRepeat]) }}"
            class="block"
            onsubmit="return confirm(@js(__('Create a repeat order from :number?', ['number' => $latestOrderForRepeat->order_number])))"
        >
            @csrf
            <button type="submit" class="crm-360__more-item w-full text-left" role="menuitem" {!! $close !!}>
                {{ __('Repeat Order') }}
            </button>
        </form>
    @endif
@endcan

@can('payments.create')
    <a
        href="{{ route('admin.payments.create', ['customer_id' => $customer->id]) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        {!! $close !!}
    >{{ __('Receive Payment') }}</a>
@endcan

@can('viewReceivablesStatement', App\Models\Crm\Customer::class)
    <a
        href="{{ route('admin.receivables.statement', [
            'customer_id' => $customer->id,
            'from_date' => now()->subYear()->toDateString(),
            'to_date' => now()->toDateString(),
        ]) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        {!! $close !!}
    >{{ __('View Statement') }}</a>
@endcan

@can('crm.customers.view')
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('print-specifications'); open = false"
    >{{ __('Print Specifications') }}</button>
@endcan

@can('sales_orders.view')
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    >{{ __('View Orders') }}</button>
@endcan

@can('invoices.view')
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    >{{ __('View Invoices') }}</button>
@endcan

@can('invoices.create')
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    >{{ __('Create invoice') }}</button>
@endcan

@can('create', App\Models\Crm\CustomerActivity::class)
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('activities'); open = false"
    >{{ __('Schedule follow-up') }}</button>
@endcan

@can('update', $customer)
    <a
        href="{{ route('admin.crm.customers.edit', $customer) }}"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        {!! $close !!}
    >{{ __('Assign account manager') }}</a>
@endcan

<hr class="crm-360__more-divider">

<a
    href="{{ route('admin.crm.customers.edit', $customer) }}"
    class="crm-360__more-item"
    role="menuitem"
    data-turbo-frame="erp-main"
    {!! $close !!}
>{{ __('View full profile') }}</a>
