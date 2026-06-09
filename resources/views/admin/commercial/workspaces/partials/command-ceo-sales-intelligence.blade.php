@php
    $intel = $ceoSalesIntelligence ?? null;
@endphp

@if (! empty($intel))
    <section class="mb-6" aria-label="{{ __('CEO sales intelligence') }}">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('CEO Sales Intelligence') }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ __('Conversion, performance, lost business, artwork impact, and production readiness from live commercial data.') }}</p>
            </div>
        </div>

        @if (! empty($intel['executive_summary']['items']))
            <x-admin.card class="mb-4">
                <div class="border-b border-erp-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Executive Summary') }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                    @foreach ($intel['executive_summary']['items'] as $item)
                        <div @class([
                            'rounded-lg border px-4 py-3',
                            'border-emerald-200 bg-emerald-50' => ($item['variant'] ?? '') === 'success',
                            'border-amber-200 bg-amber-50' => ($item['variant'] ?? '') === 'warning',
                            'border-red-200 bg-red-50' => ($item['variant'] ?? '') === 'danger',
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-erp-primary">{{ $item['label'] }}</p>
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                    'bg-emerald-100 text-emerald-800' => ($item['variant'] ?? '') === 'success',
                                    'bg-amber-100 text-amber-800' => ($item['variant'] ?? '') === 'warning',
                                    'bg-red-100 text-red-800' => ($item['variant'] ?? '') === 'danger',
                                ])>{{ $item['status_label'] }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-700">{{ $item['summary'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @endif

        <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
            @if (! empty($intel['quote_conversion']['funnel']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Quote Conversion') }}</h3>
                        @if (! empty($intel['quote_conversion']['conversion_label']))
                            <span class="text-xs font-medium text-slate-500">{{ __('Conversion') }}: <span class="font-bold text-erp-primary">{{ $intel['quote_conversion']['conversion_label'] }}</span></span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($intel['quote_conversion']['funnel'] as $step)
                            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-3 text-center">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $step['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ number_format($step['count']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if (! empty($intel['production_readiness']['items']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Production Readiness') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-3">
                        @foreach ($intel['production_readiness']['items'] as $item)
                            <div @class([
                                'rounded-lg border px-3 py-3',
                                'border-emerald-200 bg-emerald-50' => ($item['variant'] ?? '') === 'success',
                                'border-amber-200 bg-amber-50' => ($item['variant'] ?? '') === 'warning',
                                'border-erp-border bg-slate-50' => ($item['variant'] ?? '') === 'neutral',
                            ])>
                                <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">{{ $item['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($item['count']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>

        @if (! empty($intel['sales_performance']))
            <x-admin.card class="mb-4">
                <div class="border-b border-erp-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Sales Performance') }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 p-4 xl:grid-cols-2">
                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Top Sales Staff') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="erp-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Staff') }}</th>
                                        <th>{{ __('Revenue') }}</th>
                                        <th>{{ __('Orders') }}</th>
                                        <th>{{ __('Conversion') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($intel['sales_performance']['top_staff'] ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['salesperson'] }}</td>
                                            <td class="font-mono">{{ $row['revenue'] }}</td>
                                            <td>{{ $row['orders'] }}</td>
                                            <td>{{ $row['conversion'] ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-4 text-center text-slate-500">{{ __('No sales staff data for this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Top Customers') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="erp-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Revenue') }}</th>
                                        <th>{{ __('Orders') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($intel['sales_performance']['top_customers'] ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['customer'] }}</td>
                                            <td class="font-mono">{{ $row['revenue'] }}</td>
                                            <td>{{ $row['orders'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No customer revenue for this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Top Products') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="erp-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Revenue') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($intel['sales_performance']['top_products'] ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="font-mono">{{ $row['revenue'] }}</td>
                                            <td>{{ $row['quantity'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No product sales for this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Top Categories') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="erp-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Revenue') }}</th>
                                        <th>{{ __('Quotes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($intel['sales_performance']['top_categories'] ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="font-mono">{{ $row['revenue'] }}</td>
                                            <td>{{ $row['quotes'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No category mix for this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        @endif

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @if (! empty($intel['lost_business']['summary']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Lost Business Analysis') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-3">
                        @foreach ($intel['lost_business']['summary'] as $item)
                            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ number_format($item['count']) }}</p>
                                @if (! empty($item['amount']))
                                    <p class="mt-1 text-sm font-mono text-slate-600">{{ $item['amount'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if (! empty($intel['artwork_impact']))
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ __('Artwork Impact') }}</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">{{ __('Jobs Delayed by Artwork') }}</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums">{{ $intel['artwork_impact']['delayed_label'] ?? '0' }}</p>
                        </div>
                        <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Average Artwork Approval Time') }}</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $intel['artwork_impact']['avg_approval_time'] ?? '—' }}</p>
                        </div>
                    </div>
                </x-admin.card>
            @endif
        </div>
    </section>
@endif
