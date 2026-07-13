<x-admin-layout :title="__('Variance reason codes')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
    ['label' => __('Variance reason codes')],
]">
    <x-admin.page-header :title="__('Variance reason codes')" :description="__('Structured reasons for stock count variances and reconciliation.')">
        @can('create', App\Models\Inventory\InventoryVarianceReasonCode::class)
            <x-slot name="actions">
                <a href="{{ route('admin.inventory.variance-reason-codes.create') }}" class="erp-btn-primary">{{ __('New reason code') }}</a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="search" name="search" value="{{ $search }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Code or name…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
                <option value="all" @selected($status === 'all')>{{ __('All statuses') }}</option>
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :searchable="false"
        export-filename="variance-reason-codes"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Category') }}</th>
                <th scope="col">{{ __('Comment') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
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
                        <x-admin.status-badge :variant="$code->is_active ? 'success' : 'neutral'">
                            {{ $code->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $code)
                                <x-admin.table-row-action :href="route('admin.inventory.variance-reason-codes.edit', $code)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No reason codes yet')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$codes" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
