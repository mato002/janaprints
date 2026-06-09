<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Quotation list') }}</h2>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.crm.leads.360.partials.quotation-actions')
            </div>
        </div>

        @if ($quotationActions['auto_convert_enabled'] && $quotationActions['needs_customer'])
            <p class="mb-4 text-sm text-slate-600">{{ __('Auto-convert is enabled. Creating a quotation will automatically create and link a customer from this lead.') }}</p>
        @endif

        @can('quotations.view')
            <table class="erp-table text-sm w-full">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quote)
                        <tr>
                            <td>{{ $quote['number'] }}</td>
                            <td>{{ $quote['status'] }}</td>
                            <td>{{ $quote['total'] }}</td>
                            <td>{{ $quote['date']?->format('d M Y') }}</td>
                            <td>
                                <a href="{{ $quote['url'] }}" class="text-erp-accent hover:underline text-sm" data-turbo-frame="erp-main">{{ __('View quotation') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-slate-500 py-4">{{ __('No quotations linked to this lead yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <p class="crm-360__empty-inline">{{ __('You do not have permission to view quotations') }}</p>
        @endcan
    </section>
</div>
