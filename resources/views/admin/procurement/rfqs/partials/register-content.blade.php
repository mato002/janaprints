@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Requests For Quotation')">
        <x-slot name="actions">
            <a href="{{ route('admin.procurement.vendor-comparison.index') }}" class="erp-btn-secondary">{{ __('Compare quotes') }}</a>
            <a href="{{ route('admin.procurement.quotations.index') }}" class="erp-btn-secondary">{{ __('Supplier quotations') }}</a>
        </x-slot>
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Requests For Quotation') }}</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.procurement.vendor-comparison.index') }}" class="erp-btn-secondary text-sm">{{ __('Compare quotes') }}</a>
            <a href="{{ route('admin.procurement.quotations.index') }}" class="erp-btn-secondary text-sm">{{ __('Supplier quotations') }}</a>
        </div>
    </div>
@endif

<x-admin.data-table
    :search-placeholder="__('Search RFQs…')"
    export-filename="rfqs"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('RFQ #') }}</th>
            <th scope="col">{{ __('Request') }}</th>
            <th scope="col">{{ __('Issue') }}</th>
            <th scope="col">{{ __('Closing') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($rfqs as $rfq)
            @php
                $search = strtolower(($rfq->rfq_number ?? '').' '.($rfq->purchaseRequest?->request_number ?? '').' '.$rfq->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-mono font-medium">{{ $rfq->rfq_number }}</td>
                <td>{{ $rfq->purchaseRequest?->request_number ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $rfq->issue_date?->format('Y-m-d') }}</td>
                <td class="whitespace-nowrap">{{ $rfq->closing_date?->format('Y-m-d') ?? '—' }}</td>
                <td>{{ str($rfq->status->value)->headline() }}</td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.procurement.rfqs.show', $rfq)">{{ __('View') }}</x-admin.table-row-action>
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state icon="document-text" :title="__('No RFQs yet')" :description="__('RFQs appear when purchase requests are sent for quotation.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$rfqs" /></x-slot>
</x-admin.data-table>
