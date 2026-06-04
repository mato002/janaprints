<x-admin-layout :title="__('New supplier bill')">
    <x-admin.page-header :title="__('Manual supplier bill')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.payables.bills.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ tenant()->branchId() ?? auth()->user()->default_branch_id }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="erp-label">{{ __('Supplier') }}</label>
                    <select name="vendor_id" class="erp-input" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="erp-label">{{ __('Bill date') }}</label><input type="date" name="bill_date" value="{{ now()->toDateString() }}" class="erp-input" required></div>
                <div><label class="erp-label">{{ __('Due date') }}</label><input type="date" name="due_date" class="erp-input"></div>
            </div>
            <div class="border border-erp-border rounded-lg p-4 space-y-3" x-data="{ lines: [{ item_name: '', line_type: 'inventory', quantity: 1, unit_cost: 0, tax_rate: 16 }] }">
                <template x-for="(line, i) in lines" :key="i">
                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                        <input type="text" :name="'lines['+i+'][item_name]'" x-model="line.item_name" class="erp-input sm:col-span-2" placeholder="{{ __('Description') }}" required>
                        <select :name="'lines['+i+'][line_type]'" x-model="line.line_type" class="erp-input">
                            <option value="inventory">{{ __('Inventory') }}</option>
                            <option value="expense">{{ __('Expense') }}</option>
                        </select>
                        <input type="number" step="0.0001" :name="'lines['+i+'][quantity]'" x-model="line.quantity" class="erp-input" required>
                        <input type="number" step="0.01" :name="'lines['+i+'][unit_cost]'" x-model="line.unit_cost" class="erp-input" required>
                        <input type="number" step="0.01" :name="'lines['+i+'][tax_rate]'" x-model="line.tax_rate" class="erp-input">
                    </div>
                </template>
                <button type="button" class="erp-btn-secondary text-xs" @click="lines.push({ item_name: '', line_type: 'inventory', quantity: 1, unit_cost: 0, tax_rate: 16 })">{{ __('Add line') }}</button>
            </div>
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
