<x-admin-layout :title="__('Bill from GRN')">
    <x-admin.page-header :title="__('Bill from :grn', ['grn' => $receipt->receipt_number])" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.payables.bills.store-from-goods-receipt', $receipt) }}">
            @csrf
            <button class="erp-btn-primary">{{ __('Create draft bill from receipt lines') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
