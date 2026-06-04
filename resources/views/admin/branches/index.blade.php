<x-admin-layout :title="__('Branches')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Branches')]]">
    <x-admin.page-header :title="__('Branches')">
        <x-slot name="actions">
            @can('create', App\Models\Branch::class)
                <a href="{{ route('admin.branches.create') }}" class="erp-btn-primary">{{ __('Create branch') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search branches…')" export-filename="branches">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Branch') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Company') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($branches as $branch)
                <tr x-show="rowVisible(@js(strtolower($branch->company->name.' '.$branch->name.' '.$branch->code)))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $branch->name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $branch->code }}</div>
                    </td>
                    <td class="hidden md:table-cell">{{ $branch->company->name }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $branch)
                                <x-admin.table-row-action :href="route('admin.branches.edit', $branch)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state icon="location-marker" :title="__('No branches yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$branches" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
