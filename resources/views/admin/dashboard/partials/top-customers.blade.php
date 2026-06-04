<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Top Customers') }}</h2></div>
    <div class="exec-table-scroll">
        <table class="exec-table">
            <thead>
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th class="text-right">{{ __('Orders') }}</th>
                    <th class="text-right">{{ __('Revenue') }}</th>
                    <th class="text-right">{{ __('Outstanding') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['top_customers'] as $customer)
                    <tr>
                        <td>
                            @if ($customer['route'])
                                <a href="{{ $customer['route'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:underline">{{ $customer['name'] }}</a>
                            @else
                                {{ $customer['name'] }}
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $customer['orders'] }}</td>
                        <td class="text-right tabular-nums">{{ $customer['revenue'] }}</td>
                        <td class="text-right tabular-nums text-slate-500">{{ $customer['outstanding'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-xs text-slate-500">{{ __('No customer sales this month.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
