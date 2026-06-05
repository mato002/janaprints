<x-admin-layout :title="$priceBook->name" :breadcrumbs="[['label' => __('Price Books'), 'url' => route('admin.commercial.price-books.index')], ['label' => $priceBook->name]]">
    <x-admin.page-header :title="$priceBook->name" :description="$priceBook->description">
        <x-slot name="actions">
            @can('update', $priceBook)
                <a href="{{ route('admin.commercial.price-books.edit', $priceBook) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <x-admin.card class="p-4"><div class="text-xs text-slate-500">{{ __('Code') }}</div><div class="font-mono">{{ $priceBook->code }}</div></x-admin.card>
        <x-admin.card class="p-4"><div class="text-xs text-slate-500">{{ __('Status') }}</div><div>{{ $priceBook->status->label() }}</div></x-admin.card>
        <x-admin.card class="p-4"><div class="text-xs text-slate-500">{{ __('Default') }}</div><div>{{ $priceBook->is_default ? __('Yes') : __('No') }}</div></x-admin.card>
        <x-admin.card class="p-4"><div class="text-xs text-slate-500">{{ __('Items') }}</div><div>{{ $priceBook->items->count() }}</div></x-admin.card>
    </div>

    @can('update', $priceBook)
        <x-admin.card class="mb-4 p-4">
            <h3 class="mb-3 font-semibold">{{ __('Add price book item') }}</h3>
            <form method="POST" action="{{ route('admin.commercial.price-books.items.store', $priceBook) }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                @csrf
                <select name="inventory_item_id" class="erp-input">
                    <option value="">{{ __('Inventory item') }}</option>
                    @foreach ($inventoryItems as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="unit_price" class="erp-input" placeholder="{{ __('Unit price') }}" required>
                <input type="number" step="0.0001" name="minimum_quantity" class="erp-input" placeholder="{{ __('Min qty') }}">
                <button type="submit" class="erp-btn-primary">{{ __('Add item') }}</button>
            </form>
        </x-admin.card>

        <x-admin.card class="mb-4 p-4">
            <h3 class="mb-3 font-semibold">{{ __('Assign customer') }}</h3>
            <form method="POST" action="{{ route('admin.commercial.price-books.assign-customer', $priceBook) }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @csrf
                <select name="customer_id" class="erp-input" required>
                    <option value="">{{ __('Customer') }}</option>
                    @foreach ($priceBook->customerAssignments as $assignment)
                        {{-- list populated via controller on create/edit; show uses assignments below --}}
                    @endforeach
                    @php($customers = \App\Models\Crm\Customer::query()->where('company_id', $priceBook->company_id)->orderBy('company_name')->get(['id', 'company_name']))
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary">{{ __('Assign') }}</button>
            </form>
        </x-admin.card>
    @endcan

    <x-admin.card class="mb-4">
        <div class="border-b border-erp-border px-4 py-3 font-semibold">{{ __('Price book items') }}</div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Unit price') }}</th><th>{{ __('Min qty') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($priceBook->items as $item)
                        <tr>
                            <td>{{ $item->inventoryItem?->item_name ?? $item->service_code ?? $item->description }}</td>
                            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>{{ $item->minimum_quantity ?? '—' }}</td>
                            <td>{{ $item->status->label() }}</td>
                            <td>
                                @can('update', $priceBook)
                                    <form method="POST" action="{{ route('admin.commercial.price-books.items.destroy', [$priceBook, $item]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600">{{ __('Remove') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No items yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3 font-semibold">{{ __('Customer assignments') }}</div>
        <ul class="divide-y divide-erp-border">
            @forelse ($priceBook->customerAssignments as $assignment)
                <li class="px-4 py-3 text-sm">{{ $assignment->customer?->company_name }} — {{ $assignment->status->label() }}</li>
            @empty
                <li class="px-4 py-6 text-center text-slate-500">{{ __('No customer assignments.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</x-admin-layout>
