@php
    $commercialCards = [
        ['key' => 'quotations', 'label' => __('Quotes'), 'permission' => 'quotations.view'],
        ['key' => 'orders', 'label' => __('Sales Orders'), 'permission' => 'sales_orders.view'],
        ['key' => 'print_specifications', 'label' => __('Print Specifications'), 'permission' => 'crm.customers.view'],
        ['key' => 'artwork', 'label' => __('Artwork Requests'), 'permission' => 'artwork.view'],
        ['key' => 'invoices', 'label' => __('Invoices'), 'permission' => 'invoices.view'],
        ['key' => 'payments', 'label' => __('Payments'), 'permission' => 'payments.view'],
        ['key' => 'receipts', 'label' => __('Receipts'), 'permission' => 'payments.view'],
    ];
@endphp

<div class="crm-360__tab-toolbar">
    @include('admin.crm.customers.360.partials.customer-actions-dropdown', [
        'customer' => $customer,
        'latestOrderForRepeat' => $latestOrderForRepeat ?? null,
        'buttonClass' => 'crm-360__btn crm-360__btn--outline min-h-[2.75rem]',
    ])
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

@if (! empty($commercial['intelligence']))
    <x-admin.card class="mb-6">
        <h3 class="text-sm font-semibold mb-3">{{ __('Commercial summary') }}</h3>
        <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-3 lg:grid-cols-6">
            <div><dt class="text-slate-500">{{ __('Total orders') }}</dt><dd class="font-semibold">{{ $commercial['intelligence']['total_orders'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Revenue') }}</dt><dd class="font-mono">{{ number_format($commercial['intelligence']['total_revenue'], 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Payments') }}</dt><dd class="font-mono">{{ number_format($commercial['intelligence']['total_payments'], 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Outstanding') }}</dt><dd class="font-mono">{{ number_format($commercial['financial_summary']['outstanding'] ?? 0, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Est. profit') }}</dt><dd class="font-mono">{{ number_format($commercial['intelligence']['estimated_profit'], 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Margin') }}</dt><dd>{{ number_format($commercial['intelligence']['estimated_margin_percent'], 1) }}%</dd></div>
        </dl>
    </x-admin.card>
@endif

@if (! empty($commercial['recent_jobs']))
    <x-admin.card class="mb-6">
        <h3 class="text-sm font-semibold mb-3">{{ __('Recent jobs') }}</h3>
        <div class="crm-360__table-scroll">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Status') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                <tbody>
                    @foreach ($commercial['recent_jobs'] as $job)
                        <tr>
                            <td>{{ $job['job_number'] }}</td>
                            <td>{{ $job['status'] }}</td>
                            <td class="font-mono">{{ number_format($job['revenue'], 2) }}</td>
                            <td class="font-mono">{{ number_format($job['estimated_profit'], 2) }}</td>
                            <td>{{ number_format($job['estimated_margin_percent'], 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endif

<div class="crm-360__grid crm-360__grid--commercial">
    @foreach ([
        ['key' => 'quotations', 'title' => __('Quotations'), 'permission' => 'quotations.view', 'type' => 'standard'],
        ['key' => 'orders', 'title' => __('Sales orders'), 'permission' => 'sales_orders.view', 'type' => 'standard'],
        ['key' => 'artwork', 'title' => __('Artwork requests'), 'permission' => 'artwork.view', 'type' => 'standard'],
        ['key' => 'invoices', 'title' => __('Invoices'), 'permission' => 'invoices.view', 'type' => 'standard'],
        ['key' => 'payments', 'title' => __('Payments'), 'permission' => 'payments.view', 'type' => 'standard'],
        ['key' => 'receipts', 'title' => __('Receipts'), 'permission' => 'payments.view', 'type' => 'receipts'],
    ] as $section)
        @can($section['permission'])
            <section class="crm-360__card">
                <h2 class="crm-360__card-title">{{ $section['title'] }}</h2>
                <div class="crm-360__table-scroll">
                    <table class="crm-360__table">
                        <thead>
                            <tr>
                                <th>{{ __('Reference') }}</th>
                                @if ($section['type'] === 'receipts')
                                    <th>{{ __('Payment') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                @endif
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                @if ($section['key'] === 'orders')
                                    <th class="text-right">{{ __('Actions') }}</th>
                                @endif
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
                                    @if ($section['type'] === 'receipts')
                                        <td>
                                            <a href="{{ $row['payment_url'] }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $row['payment_number'] }}</a>
                                        </td>
                                        <td class="font-mono">{{ number_format($row['amount'], 2) }}</td>
                                    @endif
                                    <td>
                                        @if (! empty($row['status_value']))
                                            <x-admin.enum-status-badge :status="$row['status_value']" />
                                        @else
                                            {{ $row['status'] }}
                                        @endif
                                    </td>
                                    <td class="text-slate-500">{{ $row['date']?->format('d M Y') ?? '—' }}</td>
                                    @if ($section['key'] === 'orders')
                                        <td class="text-right">
                                            @include('admin.crm.customers.360.partials.repeat-order-form', [
                                                'customer' => $customer,
                                                'orderId' => $row['id'],
                                                'orderNumber' => $row['number'],
                                            ])
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $section['key'] === 'orders' ? 4 : ($section['type'] === 'receipts' ? 5 : 3) }}" class="crm-360__empty-inline">{{ __('No data yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endcan
    @endforeach
</div>
