@if (! empty($tabData['restricted']))
    <x-admin.empty-state :title="__('Access restricted')" :description="__('You need quotation view permission to see this tab.')" />
@else
    @php($quotations = $tabData['quotations'])
    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('Number') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th>{{ __('Converted to SO') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($quotations as $quotation)
                <tr>
                    <td>
                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="font-medium text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">{{ $quotation->quotation_number }}</a>
                    </td>
                    <td>{{ $quotation->quotation_date?->format('Y-m-d') }}</td>
                    <td><x-admin.enum-status-badge :status="$quotation->status->value" /></td>
                    <td class="text-end tabular-nums">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
                    <td>{{ $quotation->sales_order_exists ? __('Yes') : __('No') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No quotations for this customer.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($quotations->hasPages())
            <x-slot:footer>
                <x-admin.table-pagination :paginator="$quotations" />
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endif
