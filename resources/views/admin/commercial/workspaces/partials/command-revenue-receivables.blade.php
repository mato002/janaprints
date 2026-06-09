@php
    $center = $revenueReceivables ?? null;
@endphp

@if (! empty($center))
    <section class="mb-6" aria-label="{{ __('Revenue and receivables center') }}">
        <div class="mb-3">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Revenue & Receivables Center') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Revenue, invoicing, collections, and debtor exposure without opening Finance.') }}</p>
        </div>

        @if (! empty($center['revenue_strip']))
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($center['revenue_strip'] as $item)
                    @if (! empty($item['href']))
                        <a href="{{ $item['href'] }}" data-turbo-frame="erp-main" class="block transition hover:opacity-90">
                            <x-admin.kpi-widget :label="$item['label']" :value="$item['value']" :icon="$item['icon']" />
                        </a>
                    @else
                        <x-admin.kpi-widget :label="$item['label']" :value="$item['value']" :icon="$item['icon']" />
                    @endif
                @endforeach
            </div>
        @endif

        @if (! empty($center['invoice_health']))
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($center['invoice_health'] as $item)
                    <x-admin.card class="p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold tabular-nums text-erp-primary">{{ number_format($item['count']) }}</p>
                        @if (! empty($item['amount']))
                            <p class="mt-1 text-sm font-mono text-slate-600">{{ $item['amount'] }}</p>
                        @endif
                    </x-admin.card>
                @endforeach
            </div>
        @endif

        <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            @if (! empty($center['receivable_aging']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Receivable Aging') }}</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-4">
                        @foreach ($center['receivable_aging'] as $bucket)
                            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $bucket['label'] }} {{ __('days') }}</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-erp-primary">{{ $bucket['amount'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if (! empty($center['payment_visibility']['summary']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Sales Order Payment Status') }}</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-3 p-4">
                        @foreach ($center['payment_visibility']['summary'] as $item)
                            <div @class([
                                'rounded-lg border px-3 py-3',
                                'border-emerald-200 bg-emerald-50' => ($item['variant'] ?? '') === 'success',
                                'border-amber-200 bg-amber-50' => ($item['variant'] ?? '') === 'warning',
                                'border-red-200 bg-red-50' => ($item['variant'] ?? '') === 'danger',
                            ])>
                                <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">{{ $item['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($item['count']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>

        <x-admin.card class="mb-4">
            <div class="border-b border-erp-border px-4 py-3">
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('Top Debtors') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Outstanding') }}</th>
                            <th>{{ __('Last Payment') }}</th>
                            <th>{{ __('Days Outstanding') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($center['top_debtors'] ?? [] as $debtor)
                            <tr>
                                <td>
                                    @if (! empty($debtor['customer_url']))
                                        <a href="{{ $debtor['customer_url'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $debtor['customer'] }}</a>
                                    @else
                                        {{ $debtor['customer'] }}
                                    @endif
                                </td>
                                <td class="font-mono">{{ $debtor['outstanding'] }}</td>
                                <td>{{ $debtor['last_payment'] }}</td>
                                <td>{{ $debtor['days_label'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">{{ __('No outstanding debtors.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        @if (! empty($center['payment_visibility']['orders']))
            <x-admin.card>
                <div class="border-b border-erp-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Recent Sales Orders — Payment Visibility') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Order') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Outstanding') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($center['payment_visibility']['orders'] as $order)
                                <tr>
                                    <td>
                                        @if (! empty($order['url']))
                                            <a href="{{ $order['url'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $order['reference'] }}</a>
                                        @else
                                            {{ $order['reference'] }}
                                        @endif
                                    </td>
                                    <td>{{ $order['customer'] }}</td>
                                    <td>
                                        <span @class([
                                            'inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                            'bg-emerald-100 text-emerald-800' => ($order['variant'] ?? '') === 'success',
                                            'bg-amber-100 text-amber-800' => ($order['variant'] ?? '') === 'warning',
                                            'bg-red-100 text-red-800' => ($order['variant'] ?? '') === 'danger',
                                        ])>{{ $order['label'] }}</span>
                                    </td>
                                    <td class="font-mono">{{ $order['amount_paid'] }}</td>
                                    <td class="font-mono">{{ $order['amount_outstanding'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @endif
    </section>
@endif
