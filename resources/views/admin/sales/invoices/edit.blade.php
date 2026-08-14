<x-admin-layout :title="__('Edit invoice')" :breadcrumbs="[
    ['label' => __('Invoices'), 'url' => route('admin.invoices.index')],
    ['label' => $invoice->invoice_number, 'url' => route('admin.invoices.show', $invoice)],
    ['label' => __('Edit')],
]">
    <x-admin.page-header :title="__('Edit :number', ['number' => $invoice->invoice_number])" />

    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-6 max-w-3xl">
        @csrf
        @method('PUT')
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="erp-label">{{ __('Invoice date') }}</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->toDateString()) }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Due date') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->toDateString()) }}" min="{{ now()->toDateString() }}" class="erp-input w-full">
                </div>
            </div>
            <div class="mt-4">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Lines') }}</h3>
            @foreach ($invoice->lines as $index => $line)
                <div class="grid gap-2 sm:grid-cols-6 py-3 border-t border-erp-border text-sm">
                    <input type="hidden" name="items[{{ $index }}][sales_order_item_id]" value="{{ $line->sales_order_item_id }}">
                    <div class="sm:col-span-2">
                        <input type="text" name="items[{{ $index }}][item_name]" value="{{ $line->item_name }}" class="erp-input w-full" required>
                    </div>
                    <div><input type="number" name="items[{{ $index }}][quantity]" value="{{ $line->quantity }}" step="0.001" min="0.001" class="erp-input w-full" required></div>
                    <div><input type="number" name="items[{{ $index }}][unit_price]" value="{{ $line->unit_price }}" step="0.01" min="0" class="erp-input w-full" required></div>
                    <div><input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $line->tax_rate }}" step="0.01" min="0" max="100" class="erp-input w-full"></div>
                    <div><input type="number" name="items[{{ $index }}][discount]" value="{{ $line->discount }}" step="0.01" min="0" class="erp-input w-full"></div>
                </div>
            @endforeach
        </x-admin.card>

        <button type="submit" class="erp-btn-primary">{{ __('Save draft') }}</button>
    </form>
</x-admin-layout>
