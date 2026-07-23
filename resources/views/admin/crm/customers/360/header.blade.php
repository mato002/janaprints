@php
    $statusValue = $customer->status->value;
    $statusLabel = ucfirst($statusValue);
    $typeLabel = ucfirst($customer->customer_type->value);
    $contactBits = collect([
        $customer->contact_person ? __('Contact').': '.$customer->contact_person : null,
        $customer->phone ?: null,
        $customer->email ?: null,
    ])->filter()->values();
@endphp

<header class="crm-360__header">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <x-admin.crm-btn
                variant="ghost"
                size="sm"
                :href="route('admin.crm.customers.index')"
                class="crm-360__back !px-2"
                data-turbo-frame="erp-main"
            >← {{ __('Customers') }}</x-admin.crm-btn>

            <h1 class="crm-360__title">{{ $customer->company_name }}</h1>

            <p class="crm-360__subtitle">
                <span class="font-mono text-slate-600">{{ $customer->customer_code }}</span>
                <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                <span>{{ $typeLabel }}</span>
                @if ($customer->branch)
                    <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                    <span>{{ $customer->branch->name }}</span>
                @endif
            </p>

            <p class="crm-360__since">
                <span class="crm-360__status crm-360__status--{{ $statusValue }} crm-360__status--inline">{{ $statusLabel }}</span>
                <span class="crm-360__meta-sep" aria-hidden="true">·</span>
                <span>{{ __('Customer since') }} {{ $customer->created_at?->format('M Y') ?? '—' }}</span>
            </p>

            @if ($contactBits->isNotEmpty())
                <p class="crm-360__contact-line">
                    {{ $contactBits->join(' · ') }}
                </p>
            @endif
        </div>

        @include('admin.crm.customers.360.partials.primary-actions', [
            'customer' => $customer,
            'latestOrderForRepeat' => $latestOrderForRepeat ?? null,
        ])
    </div>
</header>
