@if (! empty($topCustomers))
    <section class="mb-6" aria-label="{{ __('Top customers') }}">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Top Customers') }}</h2>
            @can('commercial.reports.customers.view')
                @if (Route::has('commercial.reports.customers.index'))
                    <a href="{{ route('commercial.reports.customers.index') }}" data-turbo-frame="erp-main" class="text-xs font-medium text-erp-accent hover:text-erp-accent-hover">
                        {{ __('Customer reports') }}
                    </a>
                @endif
            @endcan
        </div>
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Revenue') }}</th>
                            <th>{{ __('Orders') }}</th>
                            <th>{{ __('Outstanding') }}</th>
                            <th>{{ __('Margin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topCustomers as $row)
                            <tr>
                                <td class="text-slate-500">{{ $row['rank'] }}</td>
                                <td>
                                    @if (! empty($row['customer_url']))
                                        <a href="{{ $row['customer_url'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $row['customer'] }}</a>
                                    @else
                                        {{ $row['customer'] }}
                                    @endif
                                </td>
                                <td class="font-mono">{{ $row['revenue'] }}</td>
                                <td>{{ $row['orders'] }}</td>
                                <td class="font-mono">{{ $row['outstanding'] }}</td>
                                <td class="text-slate-500">{{ $row['margin'] !== null ? $row['margin'] : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </section>
@endif
