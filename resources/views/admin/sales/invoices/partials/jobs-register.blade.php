@php
    $unbilledOrders = $unbilledOrders ?? collect();
    $destinationCounts = [
        'digital' => $unbilledOrders->filter(fn ($order) => $order->production_destination?->value === 'digital')->count(),
        'offset' => $unbilledOrders->filter(fn ($order) => $order->production_destination?->value === 'offset')->count(),
        'outsource' => $unbilledOrders->filter(fn ($order) => $order->production_destination?->value === 'outsource')->count(),
        'unassigned' => $unbilledOrders->filter(fn ($order) => $order->production_destination === null)->count(),
    ];
    $unbilledChips = [
        ['id' => 'all', 'label' => __('All').' ('.$unbilledOrders->count().')'],
        ['id' => 'digital', 'label' => __('Digital').' ('.$destinationCounts['digital'].')'],
        ['id' => 'offset', 'label' => __('Offset').' ('.$destinationCounts['offset'].')'],
        ['id' => 'outsource', 'label' => __('Outsourced').' ('.$destinationCounts['outsource'].')'],
    ];
    if ($destinationCounts['unassigned'] > 0) {
        $unbilledChips[] = ['id' => 'unassigned', 'label' => __('Unassigned').' ('.$destinationCounts['unassigned'].')'];
    }
@endphp

<form id="merge-invoice-form" method="POST" action="{{ route('admin.invoices.store-from-sales-orders') }}" class="hidden">
    @csrf
    <input type="hidden" name="from" value="receivables">
    <input type="hidden" name="invoice_date" value="{{ now()->toDateString() }}">
</form>

<p class="mb-3 text-xs text-slate-500">{{ __('Unbilled jobs by production department. After you generate an invoice, it appears on Invoices.') }}</p>

<x-admin.data-table
    :selectable="true"
    :search-placeholder="__('Search unbilled jobs…')"
    table-id="unbilled-orders"
    export-filename="unbilled-jobs"
    :chips="$unbilledChips"
>
    @can('create', App\Models\Sales\CustomerInvoice::class)
        <x-slot name="bulk">
            <button
                type="button"
                class="erp-btn-primary py-1 text-xs"
                @click="
                    const form = document.getElementById('merge-invoice-form');
                    form.querySelectorAll('input[name=&quot;sales_order_ids[]&quot;]').forEach((el) => el.remove());
                    selected.forEach((id) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'sales_order_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    form.submit();
                "
            >{{ __('Generate invoice') }}</button>
        </x-slot>
    @endcan
    <x-slot name="head">
        <tr>
            <th scope="col" class="w-10 erp-table-checkbox-col">
                <input type="checkbox" class="rounded border-slate-300" @change="toggleAll($event)" aria-label="{{ __('Select all') }}">
            </th>
            <th scope="col">{{ __('Job') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col">{{ __('Department') }}</th>
            <th scope="col">{{ __('Date') }}</th>
            <th scope="col">{{ __('Remaining') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($unbilledOrders as $order)
            @php
                $destination = $order->production_destination;
                $destinationChip = $destination?->value ?? 'unassigned';
            @endphp
            <tr
                data-row-id="{{ $order->getRouteKey() }}"
                x-show="rowVisible(@js(strtolower($order->order_number.' '.($order->customer?->company_name ?? '').' '.$order->status->value.' '.($destination?->label() ?? ''))), @js($destinationChip))"
            >
                <td class="erp-table-checkbox-col">
                    <input
                        type="checkbox"
                        value="{{ $order->getRouteKey() }}"
                        class="row-select rounded border-slate-300"
                        @change="toggleRow(@js($order->getRouteKey()), $event)"
                    >
                </td>
                <td class="font-mono text-sm">{{ $order->order_number }}</td>
                <td class="text-sm">{{ $order->customer?->company_name ?? '—' }}</td>
                <td class="text-sm">
                    @if ($destination)
                        <span class="erp-badge">{{ $destination->label() }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="text-sm">{{ $order->order_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="font-mono text-sm">{{ number_format($order->remainingInvoiceTotal(), 2) }}</td>
                <td><x-admin.enum-status-badge :status="$order->status->value" /></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        @can('create', App\Models\Sales\CustomerInvoice::class)
                            <x-admin.table-row-action :href="route('admin.invoices.from-sales-order', [$order, 'from' => 'receivables'])" data-erp-modal-open>
                                {{ __('Create invoice') }}
                            </x-admin.table-row-action>
                        @endcan
                        <x-admin.table-row-action :href="route('admin.sales-orders.show', [$order, 'from' => 'receivables'])" data-erp-modal-open>
                            {{ __('View order') }}
                        </x-admin.table-row-action>
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-admin.empty-state
                        icon="clipboard-list"
                        :title="__('No jobs to bill')"
                        :description="__('Jobs with a remaining balance appear here. Issued invoices are on the Invoices page.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
