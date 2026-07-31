<x-admin-layout :title="$title" :breadcrumbs="[['label' => __('Reports & Intelligence'), 'url' => route('admin.workspaces.reports')], ['label' => $title]]">
    <x-admin.page-header :title="$title" :description="$description" />

    @include('admin.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'can_export' => false,
    ])

    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach ($executive['kpis'] ?? [] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" :icon="$kpi['icon']" />
        @endforeach
    </div>

    <nav class="mb-4 flex flex-wrap gap-1 border-b border-erp-border">
        @foreach ($tabs as $tabItem)
            <a
                href="{{ route('admin.reports.commercial-intelligence', array_merge(request()->query(), ['tab' => $tabItem['key']])) }}"
                class="px-3 py-2 text-sm font-medium {{ $tab === $tabItem['key'] ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-600 hover:text-slate-900' }}"
                data-turbo-frame="erp-main"
            >{{ __($tabItem['label']) }}</a>
        @endforeach
    </nav>

    @if ($tab === 'job_profitability')
        @include('admin.reports.commercial-intelligence.partials.job-profitability', ['jobs' => $data['jobs'] ?? null])
    @elseif ($tab === 'customer_profitability')
        @include('admin.reports.commercial-intelligence.partials.customer-profitability', ['data' => $data])
    @elseif ($tab === 'product_profitability')
        @include('admin.reports.commercial-intelligence.partials.product-profitability', ['data' => $data])
    @elseif ($tab === 'branch_profitability')
        @include('admin.reports.commercial-intelligence.partials.branch-profitability', ['branches' => $data['branches'] ?? []])
    @elseif ($tab === 'outsource_profitability')
        @include('admin.reports.commercial-intelligence.partials.outsource-profitability', ['data' => $data])
    @elseif ($tab === 'waste_intelligence')
        @include('admin.reports.commercial-intelligence.partials.waste-intelligence', ['waste' => $data])
    @endif

    @if ($tab === 'job_profitability')
        <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-admin.card>
                <div class="border-b border-erp-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Top Customers') }}</h3>
                </div>
                <div class="overflow-x-auto p-4">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Revenue') }}</th>
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($executive['top_customers'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['customer_name'] ?? '—' }}</td>
                                    <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                                    <td class="font-mono">{{ number_format($row['profit'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['margin_percent'] ?? 0, 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-500">{{ __('No data in scope.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="border-b border-erp-border px-4 py-3">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ __('Top Products') }}</h3>
                </div>
                <div class="overflow-x-auto p-4">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Revenue') }}</th>
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($executive['top_products'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['product_name'] ?? '—' }}</td>
                                    <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                                    <td class="font-mono">{{ number_format($row['profit'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['margin_percent'] ?? 0, 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-500">{{ __('No data in scope.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>
    @endif
</x-admin-layout>
