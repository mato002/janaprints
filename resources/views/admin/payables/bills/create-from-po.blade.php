<x-admin-layout :title="__('Bill from PO')">
    <x-admin.page-header :title="__('Bill from :po', ['po' => $order->po_number])" :description="$order->vendor?->vendor_name" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.payables.bills.store-from-purchase-order', $order) }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div><label class="erp-label">{{ __('Bill date') }}</label><input type="date" name="bill_date" value="{{ now()->toDateString() }}" class="erp-input"></div>
                <div><label class="erp-label">{{ __('VAT %') }}</label><input type="number" name="default_tax_rate" value="16" class="erp-input"></div>
            </div>
            <p class="text-sm text-slate-500 mb-4">{{ __('PO total') }}: {{ number_format($order->total_amount, 2) }}</p>
            <button class="erp-btn-primary">{{ __('Create draft bill') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
