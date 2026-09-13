@php
    $customer = $customer ?? null;
    $customers = $customers ?? collect();
    $unbilledOrders = $unbilledOrders ?? collect();
    $customerQuery = $customerQuery ?? '';
@endphp

<x-admin-layout :title="__('Customer invoices')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Invoices')]]">
    <x-admin.page-header :title="__('Customer invoices')" :description="__('Unbilled orders that need invoicing, plus issued invoices and their payment status.')">
        @can('create', App\Models\Sales\CustomerInvoice::class)
            <x-slot name="actions">
                <a href="{{ route('admin.invoices.create', ['from' => 'receivables']) }}" class="erp-btn-primary" data-erp-modal-open>
                    {{ __('Create invoice') }}
                </a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.invoices.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        @if (request()->boolean('embedded'))
            <input type="hidden" name="embedded" value="1">
        @endif
        <div class="min-w-[16rem] flex-1">
            <label class="erp-label" for="invoice-customer-search">{{ __('Customer') }}</label>
            <input
                id="invoice-customer-search"
                type="search"
                name="customer"
                value="{{ $customerQuery }}"
                class="erp-input w-full"
                placeholder="{{ __('Search customer to see unpaid bills…') }}"
            >
        </div>
        @if ($customers->isNotEmpty())
            <div class="min-w-[14rem]">
                <label class="erp-label" for="invoice-customer-id">{{ __('Jump to') }}</label>
                <select id="invoice-customer-id" name="customer_id" class="erp-input w-full" onchange="this.form.submit()">
                    <option value="">{{ __('All customers') }}</option>
                    @foreach ($customers as $option)
                        <option value="{{ $option->id }}" @selected((int) ($customer?->id) === (int) $option->id)>{{ $option->company_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="erp-btn-secondary">{{ __('Show bills') }}</button>
        @if ($customer || $customerQuery !== '')
            <a href="{{ route('admin.invoices.index', array_filter(['embedded' => request()->boolean('embedded') ? 1 : null])) }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
        @endif
    </form>

    @if ($customer)
        <p class="mb-3 text-sm text-slate-600">
            {{ __('Showing unbilled orders and invoices for :customer.', ['customer' => $customer->company_name]) }}
        </p>
    @endif

    <form id="merge-invoice-form" method="POST" action="{{ route('admin.invoices.store-from-sales-orders') }}" class="hidden">
        @csrf
        <input type="hidden" name="from" value="receivables">
        <input type="hidden" name="invoice_date" value="{{ now()->toDateString() }}">
    </form>

    @php
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

    <div class="mb-6">
        <h2 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('To bill') }}</h2>
        <p class="mb-3 text-xs text-slate-500">{{ __('Unbilled orders by production department. Filter Digital, Offset, or Outsourced, then generate one invoice.') }}</p>
        <x-admin.data-table
            :selectable="true"
            :search-placeholder="__('Search unbilled orders…')"
            table-id="unbilled-orders"
            export-filename="unbilled-orders"
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
                    <th scope="col">{{ __('Order') }}</th>
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
                                :title="__('No unbilled orders')"
                                :description="__('Orders with a remaining balance will appear here ready to invoice.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
        </x-admin.data-table>
    </div>

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
                        <a href="{{ route('admin.invoices.show', [$invoice, 'from' => 'receivables']) }}" class="font-mono text-sm text-erp-accent" data-erp-modal-open>{{ $invoice->invoice_number }}</a>
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
                                        :action="route('admin.invoices.approve', $invoice)"
                                        method="POST"
                                    >{{ __('Approve') }}</x-admin.table-row-action>
                                @endcan
                            @endif
                            @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Approved)
                                @can('post', $invoice)
                                    <x-admin.table-row-action
                                        :action="route('admin.invoices.post', $invoice)"
                                        method="POST"
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
                            :description="__('Select unbilled orders above and generate an invoice, or create one from a single order.')"
                        />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$invoices" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
