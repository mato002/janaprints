<x-admin-layout :title="__('POS sales')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Daily sales')]]">
    <x-admin.page-header :title="__('Daily sales')" :description="__('Sales for the selected day.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosSale::class)
                <a href="{{ route('admin.commercial.pos.create') }}" class="erp-btn-primary">{{ __('New sale') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4">
            <div>
                <label class="text-xs text-slate-500">{{ __('Date') }}</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? today()->toDateString() }}" class="erp-input mt-1">
            </div>
            <div>
                <label class="text-xs text-slate-500">{{ __('Status') }}</label>
                <select name="status" class="erp-input mt-1">
                    <option value="">{{ __('All') }}</option>
                    @foreach (App\Enums\PosSaleStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Sale #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Cashier') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="font-mono">{{ $sale->sale_number }}</td>
                            <td>{{ $sale->is_walk_in ? __('Walk-in') : ($sale->customer?->company_name ?? '—') }}</td>
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
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No sales for this day.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-erp-border px-4 py-3"><x-admin.table-pagination :paginator="$sales" /></div>
    </x-admin.card>
</x-admin-layout>
