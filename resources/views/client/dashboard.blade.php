<x-layouts.client :title="__('Overview')" :heading="__('Overview')">
    <section class="client-welcome">
        <div class="client-welcome__bg public-hero__cmyk-grid" aria-hidden="true"></div>
        <div class="client-welcome__inner">
            <span class="public-badge public-badge--light client-welcome__badge">{{ __('Welcome back') }}</span>
            <h2 class="client-welcome__title">{{ auth()->user()->name }}</h2>
            <p class="client-welcome__text">
                {{ __('Track quotes, orders, invoices, and artwork approvals for :company.', [
                    'company' => $customer->company_name,
                ]) }}
            </p>
        </div>
    </section>

    <div class="client-metrics">
        @foreach ($dashboard['metrics'] as $metric)
            @php
                $icon = match ($metric['key']) {
                    'balance' => 'currency',
                    'quotes' => 'document',
                    'orders' => 'clipboard',
                    'artwork' => 'palette',
                    default => 'home',
                };
            @endphp
            <article @class(['client-metric', 'client-metric--'.$metric['tone']])>
                <div class="client-metric__icon-wrap">
                    <x-client.icon :name="$icon" class="h-5 w-5" />
                </div>
                <p class="client-metric__label">{{ $metric['label'] }}</p>
                <p class="client-metric__value">{{ $metric['value'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="client-grid">
        <section class="client-panel">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <span class="client-panel__icon"><x-client.icon name="document" class="h-4 w-4" /></span>
                    <h2 class="client-panel__title">{{ __('Recent quotes') }}</h2>
                </div>
                <a href="{{ route('client.quotations.index') }}" class="client-panel__link">
                    {{ __('View all') }}
                    <x-client.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>
            @forelse ($dashboard['recent_quotations'] as $quotation)
                <a href="{{ route('client.quotations.show', $quotation) }}" class="client-list-item group">
                    <span class="client-list-item__primary">{{ $quotation->quotation_number }}</span>
                    <span class="client-list-item__meta">KES {{ number_format((float) $quotation->total_amount, 0) }}</span>
                    @include('client.partials.status-badge', ['status' => $quotation->status])
                    <x-client.icon name="arrow-right" class="client-list-item__chevron h-4 w-4" />
                </a>
            @empty
                @include('client.partials.empty-state', [
                    'icon' => 'document',
                    'message' => __('No quotes yet. When we send you a quote, it will appear here.'),
                    'actionLabel' => __('Browse services'),
                    'actionRoute' => route('storefront.services'),
                ])
            @endforelse
        </section>

        <section class="client-panel">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <span class="client-panel__icon"><x-client.icon name="clipboard" class="h-4 w-4" /></span>
                    <h2 class="client-panel__title">{{ __('Recent orders') }}</h2>
                </div>
                <a href="{{ route('client.orders.index') }}" class="client-panel__link">
                    {{ __('View all') }}
                    <x-client.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>
            @forelse ($dashboard['recent_orders'] as $order)
                <a href="{{ route('client.orders.show', $order) }}" class="client-list-item group">
                    <span class="client-list-item__primary">{{ $order->order_number }}</span>
                    @include('client.partials.status-badge', ['status' => $order->status])
                    <x-client.icon name="arrow-right" class="client-list-item__chevron h-4 w-4" />
                </a>
            @empty
                @include('client.partials.empty-state', [
                    'icon' => 'clipboard',
                    'message' => __('No orders in progress yet.'),
                ])
            @endforelse
        </section>

        <section class="client-panel">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <span class="client-panel__icon"><x-client.icon name="receipt" class="h-4 w-4" /></span>
                    <h2 class="client-panel__title">{{ __('Recent invoices') }}</h2>
                </div>
                <a href="{{ route('client.invoices.index') }}" class="client-panel__link">
                    {{ __('View all') }}
                    <x-client.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>
            @forelse ($dashboard['recent_invoices'] as $invoice)
                <a href="{{ route('client.invoices.show', $invoice) }}" class="client-list-item group">
                    <span class="client-list-item__primary">{{ $invoice->invoice_number }}</span>
                    <span class="client-list-item__meta">KES {{ number_format((float) $invoice->balance_due, 0) }}</span>
                    <x-client.icon name="arrow-right" class="client-list-item__chevron h-4 w-4" />
                </a>
            @empty
                @include('client.partials.empty-state', [
                    'icon' => 'receipt',
                    'message' => __('No invoices yet.'),
                ])
            @endforelse
        </section>

        <section class="client-panel">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <span class="client-panel__icon"><x-client.icon name="palette" class="h-4 w-4" /></span>
                    <h2 class="client-panel__title">{{ __('Artwork awaiting review') }}</h2>
                </div>
                <a href="{{ route('client.artwork.index') }}" class="client-panel__link">
                    {{ __('View all') }}
                    <x-client.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>
            @forelse ($dashboard['pending_artwork'] as $artwork)
                <a href="{{ route('client.artwork.show', $artwork) }}" class="client-list-item group">
                    <span class="client-list-item__primary">{{ $artwork->request_number }} — {{ $artwork->title }}</span>
                    @include('client.partials.status-badge', ['status' => $artwork->status])
                    <x-client.icon name="arrow-right" class="client-list-item__chevron h-4 w-4" />
                </a>
            @empty
                @include('client.partials.empty-state', [
                    'icon' => 'palette',
                    'message' => __('Nothing pending review right now.'),
                ])
            @endforelse
        </section>
    </div>
</x-layouts.client>
