<x-admin-layout
    :title="__('Create invoice')"
    :breadcrumbs="[
        ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
        ['label' => __('Invoices'), 'url' => route('admin.invoices.index')],
        ['label' => __('Create invoice')],
    ]"
>
    <x-admin.page-header
        :title="__('Create invoice')"
        :description="__('Select a sales order with a remaining billable balance. Invoices are always created from confirmed orders.')"
    >
        <x-slot name="secondary">
            <a href="{{ route('admin.invoices.index') }}" class="erp-btn-secondary" data-turbo-frame="erp-main" data-turbo-action="advance">
                {{ __('Back to invoices') }}
            </a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false">
        <form method="GET" action="{{ route('admin.invoices.create') }}" class="flex flex-wrap items-end gap-3 border-b border-erp-border px-4 py-3">
            @if ($customerId)
                <input type="hidden" name="customer_id" value="{{ $customerId }}">
            @endif
            <div class="min-w-[16rem] flex-1">
                <label for="invoice-order-search" class="mb-1 block text-xs font-medium text-slate-600">{{ __('Search') }}</label>
                <input
                    id="invoice-order-search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    class="erp-input w-full text-sm"
                    placeholder="{{ __('Order number or customer…') }}"
                >
            </div>
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Search') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Order') }}</th>
                        <th class="px-4 py-3">{{ __('Customer') }}</th>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Order total') }}</th>
                        <th class="px-4 py-3">{{ __('Remaining') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-mono text-erp-accent">{{ $order->order_number }}</td>
                            <td class="px-4 py-3">{{ $order->customer?->company_name }}</td>
                            <td class="px-4 py-3">{{ $order->order_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-mono">{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-4 py-3 font-mono font-semibold text-erp-primary">{{ number_format($order->remainingInvoiceTotal(), 2) }}</td>
                            <td class="px-4 py-3">
                                <x-admin.enum-status-badge :status="$order->status->value" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a
                                    href="{{ route('admin.invoices.from-sales-order', $order) }}"
                                    class="erp-btn-primary text-xs"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >{{ __('Create invoice') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8">
                                <x-admin.empty-state
                                    icon="receipt-tax"
                                    :title="__('No billable sales orders found')"
                                    :description="__('Confirm a sales order first, or check that it still has a remaining billable balance. You can also create invoices from a sales order or delivery note.')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
