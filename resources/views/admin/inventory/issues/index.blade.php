<x-admin-layout :title="__('Stock issues')">
    <x-admin.page-header :title="__('Stock issues')">
        @can('create', App\Models\Inventory\StockIssue::class)
            <a href="{{ route('admin.inventory.issues.create') }}" class="erp-btn-primary">{{ __('New issue') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search issues…')" export-filename="stock-issues">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Issue') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($issues as $i)
                <tr x-show="rowVisible(@js(strtolower($i->issue_number.' '.$i->status->value)))">
                    <td class="font-medium">{{ $i->issue_number }}</td>
                    <td><x-admin.enum-status-badge :status="$i->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.issues.show', $i)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state icon="switch-horizontal" :title="__('No issues yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$issues" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
