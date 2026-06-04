<x-admin-layout :title="__('Invoice from order')" :breadcrumbs="[
    ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
    ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
    ['label' => __('Create invoice')],
]">
    <x-admin.page-header :title="__('Create invoice')" :description="$salesOrder->order_number.' — '.__('Remaining').': '.number_format($salesOrder->remainingInvoiceTotal(), 2)" />

    <form method="POST" action="{{ route('admin.invoices.store-from-sales-order', $salesOrder) }}" class="space-y-6 max-w-2xl">
        @csrf
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="erp-label">{{ __('Billing type') }}</label>
                    <select name="invoice_type" class="erp-input w-full" x-data @change="$dispatch('billing-type-changed', $event.target.value)">
                        <option value="standard">{{ __('Full invoice') }}</option>
                        <option value="partial">{{ __('Partial (selected lines)') }}</option>
                        <option value="deposit">{{ __('Deposit') }}</option>
                        <option value="progress">{{ __('Progress billing') }}</option>
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Invoice date') }}</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Due date') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label">{{ __('Progress %') }}</label>
                    <input type="number" name="billing_percent" min="1" max="100" step="0.01" class="erp-input w-full" placeholder="30">
                </div>
                <div>
                    <label class="erp-label">{{ __('Deposit amount') }}</label>
                    <input type="number" name="deposit_amount" min="0.01" step="0.01" class="erp-input w-full">
                </div>
            </div>
            <div class="mt-4">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Lines (for partial invoicing)') }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ __('Leave unchecked for full, deposit, or progress invoices.') }}</p>
            @foreach ($salesOrder->items as $index => $item)
                <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm">
                    <input type="checkbox" name="lines[{{ $index }}][selected]" value="1" class="rounded">
                    <input type="hidden" name="lines[{{ $index }}][sales_order_item_id]" value="{{ $item->id }}">
                    <span class="flex-1 font-medium">{{ $item->item_name }}</span>
                    <span class="text-slate-500">{{ __('Max') }} {{ $item->quantity }}</span>
                    <input type="number" name="lines[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="0.001" step="0.001" class="erp-input w-24">
                </div>
            @endforeach
        </x-admin.card>

        <button type="submit" class="erp-btn-primary">{{ __('Create draft invoice') }}</button>
    </form>
</x-admin-layout>
