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

        <div class="crm-360__action-bar">
            @include('admin.crm.customers.360.partials.customer-actions-dropdown', [
                'customer' => $customer,
                'latestOrderForRepeat' => $latestOrderForRepeat ?? null,
            ])
        </div>
    </div>
</header>
