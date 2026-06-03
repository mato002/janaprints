<x-admin-layout :title="__('Branches')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Branches')]]">
    <x-admin.page-header :title="__('Branches')">
        <x-slot name="actions">
            @can('create', App\Models\Branch::class)
                <a href="{{ route('admin.branches.create') }}" class="erp-btn-primary">{{ __('Create branch') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Company') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($branches as $branch)
                <tr x-show="matches(@js($branch->company->name.' '.$branch->name.' '.$branch->code))">
                    <td>{{ $branch->company->name }}</td>
                    <td class="font-medium text-erp-primary">{{ $branch->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $branch->code }}</td>
                    <td class="text-right">
                        @can('update', $branch)
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="font-medium text-erp-accent hover:underline">{{ __('Edit') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="location-marker" :title="__('No branches yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $branches->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
