<x-admin-layout :title="__('Customers')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Customers')]]">
    <x-admin.page-header :title="__('Customers')" :description="__('Customer accounts and contacts.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\Customer::class)
                <a href="{{ route('admin.crm.customers.create') }}" class="erp-btn-primary">{{ __('Create customer') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search customers…')"
        export-filename="customers"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'active', 'label' => __('Active')],
            ['id' => 'inactive', 'label' => __('Inactive')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($customers as $customer)
                @php
                    $search = strtolower($customer->customer_code.' '.$customer->company_name.' '.($customer->branch?->name ?? '').' '.$customer->status->value);
                    $chip = strtolower($customer->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search), @js($chip))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $customer->company_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $customer->customer_code }}</div>
                    </td>
                    <td class="hidden sm:table-cell">{{ $customer->branch?->name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$customer->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.crm.customers.show', $customer)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $customer)
                                <x-admin.table-row-action :href="route('admin.crm.customers.edit', $customer)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-admin.empty-state icon="user-circle" :title="__('No customers yet')" :description="__('Start by adding your first customer account.')">
                            <x-slot name="action">
                                @can('create', App\Models\Crm\Customer::class)
                                    <a href="{{ route('admin.crm.customers.create') }}" class="erp-btn-primary">{{ __('Create customer') }}</a>
                                @endcan
                            </x-slot>
                        </x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$customers" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
