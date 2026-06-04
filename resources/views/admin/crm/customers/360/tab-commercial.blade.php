@php
    $commercialCards = [
        ['key' => 'quotations', 'label' => __('Quotes'), 'permission' => 'quotations.view'],
        ['key' => 'orders', 'label' => __('Sales Orders'), 'permission' => 'sales_orders.view'],
        ['key' => 'artwork', 'label' => __('Artwork'), 'permission' => 'artwork.view'],
        ['key' => 'invoices', 'label' => __('Invoices'), 'permission' => 'invoices.view'],
        ['key' => 'payments', 'label' => __('Payments'), 'permission' => 'payments.view'],
    ];
@endphp

<div class="crm-360__tab-toolbar">
    @can('quotations.create')
        <x-admin.crm-btn
            variant="outline"
            size="sm"
            :href="route('admin.quotations.create', ['customer_id' => $customer->id])"
            data-turbo-frame="erp-main"
        >{{ __('Create quote') }}</x-admin.crm-btn>
    @endcan
    @can('sales_orders.create')
        <x-admin.crm-btn
            variant="outline"
            size="sm"
            :href="route('admin.sales-orders.create', ['customer_id' => $customer->id])"
            data-turbo-frame="erp-main"
        >{{ __('Create sales order') }}</x-admin.crm-btn>
    @endcan
    @can('invoices.view')
        <x-admin.crm-btn
            variant="ghost"
            size="sm"
            :href="route('admin.invoices.index')"
            data-turbo-frame="erp-main"
        >{{ __('Invoices') }}</x-admin.crm-btn>
    @endcan
</div>

<div class="crm-360__commercial-summary">
    @foreach ($commercialCards as $card)
        @can($card['permission'])
            @php $count = $commercial['counts'][$card['key']] ?? null; @endphp
            <div class="crm-360__commercial-card">
                <span class="crm-360__commercial-card-label">{{ $card['label'] }}</span>
                <span class="crm-360__commercial-card-value">{{ $count !== null ? $count : '—' }}</span>
            </div>
        @endcan
    @endforeach
</div>

<div class="crm-360__grid crm-360__grid--commercial">
    @foreach ([
        ['key' => 'quotations', 'title' => __('Recent quotes'), 'permission' => 'quotations.view'],
        ['key' => 'orders', 'title' => __('Recent sales orders'), 'permission' => 'sales_orders.view'],
        ['key' => 'artwork', 'title' => __('Recent artwork'), 'permission' => 'artwork.view'],
        ['key' => 'invoices', 'title' => __('Recent invoices'), 'permission' => 'invoices.view'],
        ['key' => 'payments', 'title' => __('Recent payments'), 'permission' => 'payments.view'],
    ] as $section)
        @can($section['permission'])
            <section class="crm-360__card">
                <h2 class="crm-360__card-title">{{ $section['title'] }}</h2>
                <div class="crm-360__table-scroll">
                    <table class="crm-360__table">
                        <thead>
                            <tr>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commercial[$section['key']] as $row)
                                <tr>
                                    <td>
                                        @if ($row['url'])
                                            <a href="{{ $row['url'] }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $row['number'] }}</a>
                                        @else
                                            {{ $row['number'] }}
                                        @endif
                                    </td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-slate-500">{{ $row['date']?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="crm-360__empty-inline">{{ __('No data yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endcan
    @endforeach
</div>
