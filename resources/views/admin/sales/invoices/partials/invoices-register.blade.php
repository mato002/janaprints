<x-admin.data-table
    :search-placeholder="__('Search invoices…')"
    export-route="admin.accounting.exports"
    :export-route-params="['listing' => 'customer-invoices']"
    :export-query="request()->query()"
    :format-in-path="true"
    export-filename="customer-invoices"
    :chips="[
        ['id' => 'all', 'label' => __('All invoices')],
        ['id' => 'pending', 'label' => __('Pending')],
        ['id' => 'unpaid', 'label' => __('Unpaid')],
        ['id' => 'partial', 'label' => __('Partial')],
        ['id' => 'paid', 'label' => __('Paid')],
    ]"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Number') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col">{{ __('Orders') }}</th>
            <th scope="col">{{ __('Department') }}</th>
            <th scope="col">{{ __('Date') }}</th>
            <th scope="col">{{ __('Total') }}</th>
            <th scope="col">{{ __('Balance') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($invoices as $invoice)
            @php
                $collection = $invoice->collectionStatus();
                $linkedOrders = $invoice->salesOrders->isNotEmpty()
                    ? $invoice->salesOrders
                    : collect([$invoice->salesOrder])->filter();
                $orderNumbers = $linkedOrders->pluck('order_number')->filter()->implode(', ') ?: '—';
                $destinations = $linkedOrders
                    ->map(fn ($order) => $order?->production_destination?->label())
                    ->filter()
                    ->unique()
                    ->values();
                $departmentLabel = $destinations->isEmpty()
                    ? '—'
                    : ($destinations->count() === 1 ? $destinations->first() : $destinations->implode(', '));
            @endphp
            <tr x-show="rowVisible(@js(strtolower($invoice->invoice_number.' '.($invoice->customer?->company_name ?? '').' '.$collection->value.' '.$orderNumbers.' '.$departmentLabel)), @js($collection->value))">
                <td>
                    <a
                        href="{{ route('admin.invoices.show', [$invoice, 'from' => 'receivables']) }}"
                        class="font-mono text-sm text-erp-accent"
                        data-erp-modal-open
                        data-open-invoice="{{ $invoice->getRouteKey() }}"
                    >{{ $invoice->invoice_number }}</a>
                </td>
                <td class="text-sm">{{ $invoice->customer?->company_name }}</td>
                <td class="text-xs text-slate-600">{{ $orderNumbers }}</td>
                <td class="text-xs text-slate-600">{{ $departmentLabel }}</td>
                <td class="text-sm">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                <td class="text-sm font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                <td class="text-sm font-mono">{{ $invoice->status === App\Enums\CustomerInvoiceStatus::Posted ? number_format($invoice->balance_due, 2) : '—' }}</td>
                <td>
                    <x-admin.status-badge :variant="$collection->badgeVariant()">{{ $collection->label() }}</x-admin.status-badge>
                </td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.invoices.show', [$invoice, 'from' => 'receivables'])" data-erp-modal-open>{{ __('View') }}</x-admin.table-row-action>
                        @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Draft)
                            @can('approve', $invoice)
                                <x-admin.table-row-action
                                    :action="route('admin.invoices.approve', [$invoice, 'from' => 'receivables'])"
                                    method="POST"
                                    turbo-frame="module-workspace-content"
                                >{{ __('Approve') }}</x-admin.table-row-action>
                            @endcan
                        @endif
                        @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Approved)
                            @can('post', $invoice)
                                <x-admin.table-row-action
                                    :action="route('admin.invoices.post', [$invoice, 'from' => 'receivables'])"
                                    method="POST"
                                    turbo-frame="module-workspace-content"
                                    :confirm="__('Post this invoice to accounts receivable?')"
                                >{{ __('Post to AR') }}</x-admin.table-row-action>
                            @endcan
                        @endif
                        @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Posted && $invoice->balance_due > 0)
                            @can('create', App\Models\Sales\CustomerPayment::class)
                                <x-admin.table-row-action :href="route('admin.payments.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id, 'from' => 'receivables'])" data-erp-modal-open>
                                    {{ __('Record payment') }}
                                </x-admin.table-row-action>
                            @endcan
                        @endif
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">
                    <x-admin.empty-state
                        icon="receipt-tax"
                        :title="__('No invoices yet')"
                        :description="__('Bill jobs on the Jobs page. Generated invoices appear here.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$invoices" /></x-slot>
</x-admin.data-table>

@if (request()->filled('open_invoice'))
    <div
        hidden
        data-open-invoice-key="{{ request('open_invoice') }}"
        x-data
        x-init="
            $nextTick(() => {
                const key = $el.dataset.openInvoiceKey;
                document.querySelector('[data-open-invoice=&quot;' + key + '&quot;]')?.click();
            })
        "
    ></div>
@endif
