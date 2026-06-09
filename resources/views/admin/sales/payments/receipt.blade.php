<x-admin-layout :title="__('Receipt :number', ['number' => $receipt['receipt_number']])">
    @include('admin.sales.payments.partials.receipt-body', ['printActions' => true])
</x-admin-layout>
