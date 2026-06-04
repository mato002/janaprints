<header class="crm-360__header">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <x-admin.crm-btn
                variant="ghost"
                size="sm"
                :href="route('admin.crm.customers.index')"
                class="!px-2.5"
                data-turbo-frame="erp-main"
            >← {{ __('Customers') }}</x-admin.crm-btn>
            <h1 class="crm-360__title">{{ $customer->company_name }}</h1>
            <p class="crm-360__subtitle">
                <span class="font-mono text-slate-600">{{ $customer->customer_code }}</span>
                @if ($customer->branch)
                    <span class="text-slate-300" aria-hidden="true"> • </span>
                    <span>{{ $customer->branch->name }}</span>
                @endif
            </p>
            <p class="crm-360__since">
                {{ __('Customer since') }} {{ $customer->created_at?->format('M Y') ?? '—' }}
            </p>
            <span class="crm-360__status crm-360__status--{{ $customer->status->value }}">
                {{ strtoupper($customer->status->value) }}
            </span>
        </div>

        <div class="crm-360__action-bar" x-data="{ moreOpen: false }">
            @can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
                <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" class="inline" data-turbo-frame="erp-main">
                    @csrf
                    <button type="submit" class="crm-360__btn crm-360__btn--primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        {{ __('Start conversation') }}
                    </button>
                </form>
            @endcan

            @can('quotations.create')
                <a
                    href="{{ route('admin.quotations.create', ['customer_id' => $customer->id]) }}"
                    class="crm-360__btn crm-360__btn--outline"
                    data-turbo-frame="erp-main"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Create quote') }}
                </a>
            @endcan

            <div class="relative">
                <button
                    type="button"
                    class="crm-360__btn crm-360__btn--ghost"
                    @click="moreOpen = !moreOpen"
                    :aria-expanded="moreOpen"
                    aria-haspopup="true"
                >
                    {{ __('More') }}
                    <svg class="h-4 w-4 transition-transform" :class="moreOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div
                    x-show="moreOpen"
                    x-cloak
                    @click.outside="moreOpen = false"
                    class="crm-360__more-menu"
                    role="menu"
                >
                    @can('sales_orders.create')
                        <a
                            href="{{ route('admin.sales-orders.create', ['customer_id' => $customer->id]) }}"
                            class="crm-360__more-item"
                            role="menuitem"
                            data-turbo-frame="erp-main"
                            @click="moreOpen = false"
                        >{{ __('Create sales order') }}</a>
                    @endcan
                    @can('invoices.create')
                        <button
                            type="button"
                            class="crm-360__more-item w-full text-left"
                            role="menuitem"
                            @click="setTab('commercial'); moreOpen = false"
                        >{{ __('Create invoice') }}</button>
                    @endcan
                    @can('create', App\Models\Crm\CustomerActivity::class)
                        <button
                            type="button"
                            class="crm-360__more-item w-full text-left"
                            role="menuitem"
                            @click="setTab('activities'); moreOpen = false"
                        >{{ __('Schedule follow-up') }}</button>
                    @endcan
                    @can('update', $customer)
                        <a
                            href="{{ route('admin.crm.customers.edit', $customer) }}"
                            class="crm-360__more-item"
                            role="menuitem"
                            data-turbo-frame="erp-main"
                            @click="moreOpen = false"
                        >{{ __('Assign account manager') }}</a>
                    @endcan
                    <hr class="crm-360__more-divider">
                    <a
                        href="{{ route('admin.crm.customers.edit', $customer) }}"
                        class="crm-360__more-item"
                        role="menuitem"
                        data-turbo-frame="erp-main"
                        @click="moreOpen = false"
                    >{{ __('View full profile') }}</a>
                </div>
            </div>
        </div>
    </div>
</header>
