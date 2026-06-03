<x-admin-layout :title="__('Companies')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Companies')]]">
    <x-admin.page-header :title="__('Companies')" :description="__('Legal entities in your ERP tenant.')">
        <x-slot name="actions">
            @can('create', App\Models\Company::class)
                <a href="{{ route('admin.companies.create') }}" class="erp-btn-primary">{{ __('Create company') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($companies as $company)
                <tr x-show="matches(@js($company->name.' '.$company->code))">
                    <td class="font-medium text-erp-primary">{{ $company->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $company->code }}</td>
                    <td>
                        <x-admin.status-badge :variant="$company->is_active ? 'success' : 'danger'">
                            {{ $company->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="text-right">
                        @can('update', $company)
                            <a href="{{ route('admin.companies.edit', $company) }}" class="font-medium text-erp-accent hover:underline">{{ __('Edit') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="building" :title="__('No companies yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $companies->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
