<x-admin-layout :title="__('Variance reason codes')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
    ['label' => __('Variance reason codes')],
]">
    <x-admin.page-header :title="__('Variance reason codes')" :description="__('Structured reasons for stock count variances and reconciliation.')">
        @can('create', App\Models\Inventory\InventoryVarianceReasonCode::class)
            <a href="{{ route('admin.inventory.variance-reason-codes.create') }}" class="erp-btn-primary">{{ __('New reason code') }}</a>
        @endcan
    </x-admin.page-header>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="erp-label">{{ __('Status') }}</label>
            <select name="status" class="erp-select" onchange="this.form.submit()">
                <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
                <option value="all" @selected($status === 'all')>{{ __('All') }}</option>
            </select>
        </div>
        <div class="min-w-[12rem] flex-1">
            <label class="erp-label">{{ __('Search') }}</label>
            <input type="search" name="search" value="{{ $search }}" class="erp-input w-full" placeholder="{{ __('Code or name…') }}">
        </div>
        <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
    </form>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Comment') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($codes as $code)
                <tr>
                    <td class="font-mono text-sm">{{ $code->code }}</td>
                    <td>{{ $code->name }}</td>
                    <td>{{ $code->category->label() }}</td>
                    <td>{{ $code->requires_comment ? __('Required') : __('Optional') }}</td>
                    <td>
                        <span class="erp-badge {{ $code->is_active ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-100 text-slate-500' }}">
                            {{ $code->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        @can('update', $code)
                            <a href="{{ route('admin.inventory.variance-reason-codes.edit', $code) }}" class="erp-link">{{ __('Edit') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="clipboard-list" :title="__('No reason codes yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$codes" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
