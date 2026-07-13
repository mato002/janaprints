<x-admin-layout :title="__('POS sales')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Daily sales')]]">
    <x-admin.page-header :title="__('Daily sales')" :description="__('Sales for the selected day.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosSale::class)
                <a href="{{ route('admin.commercial.pos.counter-sales') }}" class="erp-btn-primary">{{ __('New sale') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="date" name="date" value="{{ $filters['date'] ?? today()->toDateString() }}" class="erp-toolbar-input" aria-label="{{ __('Date') }}">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PosSaleStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search sales…')"
        export-filename="pos-daily-sales"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Sale #') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Cashier') }}</th>
                <th scope="col">{{ __('Total') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($sales as $sale)
                @php
                    $customer = $sale->is_walk_in ? __('Walk-in') : ($sale->customer?->company_name ?? '—');
                    $search = strtolower($sale->sale_number.' '.$customer.' '.($sale->cashier?->name ?? '').' '.$sale->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-mono font-medium">{{ $sale->sale_number }}</td>
                    <td>{{ $customer }}</td>
                    <td>{{ $sale->cashier?->name }}</td>
                    <td class="tabular-nums">{{ number_format($sale->total_amount, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$sale->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.pos.show', $sale)">{{ __('View') }}</x-admin.table-row-action>
                            @if ($sale->status === App\Enums\PosSaleStatus::Paid)
                                <x-admin.table-row-action :href="route('admin.commercial.pos.receipt', $sale)">{{ __('Receipt') }}</x-admin.table-row-action>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="cash" :title="__('No sales for this day')" :description="__('Sales recorded for the selected date will appear here.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$sales" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
