<x-admin-layout :title="__('New supplier payment')">
    <x-admin.page-header :title="__('Record supplier payment')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.payables.payments.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ tenant()->branchId() ?? auth()->user()->default_branch_id }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="erp-label">{{ __('Supplier') }}</label>
                    <select name="vendor_id" class="erp-input" required onchange="if(this.value) window.location='{{ route('admin.payables.payments.create') }}?vendor_id='+this.value">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" @selected($vendor?->id === $v->id)>{{ $v->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="erp-label">{{ __('Payment date') }}</label><input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="erp-input" required></div>
                <div>
                    <label class="erp-label">{{ __('Method') }}</label>
                    <select name="payment_method" class="erp-input"><option value="bank">{{ __('Bank') }}</option><option value="cash">{{ __('Cash') }}</option></select>
                </div>
                <div><label class="erp-label">{{ __('Amount') }}</label><input type="number" step="0.01" name="amount" class="erp-input" required></div>
            </div>
            @if ($vendor && count($openBills) > 0)
                <h3 class="font-medium">{{ __('Allocate to bills') }}</h3>
                @foreach ($openBills as $i => $bill)
                    <div class="flex gap-2 items-center text-sm">
                        <span class="flex-1">{{ $bill->bill_number }} ({{ number_format($bill->balance_due, 2) }})</span>
                        <input type="hidden" name="allocations[{{ $i }}][supplier_bill_id]" value="{{ $bill->id }}">
                        <input type="number" step="0.01" name="allocations[{{ $i }}][amount]" value="{{ $bill->balance_due }}" class="erp-input w-32">
                    </div>
                @endforeach
            @endif
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
