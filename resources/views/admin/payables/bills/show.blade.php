<x-admin-layout :title="$bill->bill_number" :breadcrumbs="[['label' => __('Supplier bills'), 'url' => route('admin.payables.bills.index')], ['label' => $bill->bill_number]]">
    <x-admin.page-header :title="$bill->bill_number" :description="$bill->vendor?->vendor_name.' · '.$bill->status->label()" />

    <div class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Total')" :value="number_format($bill->total_amount, 2)" />
        <x-admin.kpi-widget :label="__('Paid')" :value="number_format($bill->amount_paid, 2)" />
        <x-admin.kpi-widget :label="__('Balance due')" :value="number_format($bill->balance_due, 2)" />
        <x-admin.kpi-widget :label="__('Type')" :value="$bill->bill_type->label()" />
    </div>

    <x-admin.card class="mb-4">
        <div class="flex flex-wrap gap-2">
            @can('approve', $bill)
                <form method="POST" action="{{ route('admin.payables.bills.approve', $bill) }}">@csrf<button class="erp-btn-secondary">{{ __('Approve') }}</button></form>
            @endcan
            @can('post', $bill)
                <form method="POST" action="{{ route('admin.payables.bills.post', $bill) }}">@csrf<button class="erp-btn-primary">{{ __('Post to AP') }}</button></form>
            @endcan
            @can('creditNote', $bill)
                <form method="POST" action="{{ route('admin.payables.bills.credit-note.store', $bill) }}">@csrf<button class="erp-btn-secondary">{{ __('Credit note') }}</button></form>
            @endcan
            @if ($bill->balance_due > 0 && $bill->status === App\Enums\SupplierBillStatus::Posted)
                <a href="{{ route('admin.payables.payments.create', ['vendor_id' => $bill->vendor_id, 'bill_id' => $bill->id]) }}" class="erp-btn-secondary">{{ __('Record payment') }}</a>
            @endif
        </div>
    </x-admin.card>

    <x-admin.card>
        <table class="w-full text-sm">
            @foreach ($bill->lines as $line)
                <tr class="border-t border-erp-border">
                    <td class="py-2">{{ $line->item_name }} <span class="text-slate-400 text-xs">({{ $line->line_type->label() }})</span></td>
                    <td class="text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </table>
    </x-admin.card>
</x-admin-layout>
