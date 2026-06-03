<x-admin-layout :title="__('Customers')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Customers')]]">
    <x-admin.page-header :title="__('Customers')" :description="__('Customer accounts and contacts.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\Customer::class)
                <a href="{{ route('admin.crm.customers.create') }}" class="erp-btn-primary">{{ __('Create customer') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($customers as $customer)
                <tr x-show="matches(@js($customer->customer_code.' '.$customer->company_name.' '.($customer->branch?->name ?? '').' '.$customer->status->value))">
                    <td class="font-mono text-xs text-slate-500">{{ $customer->customer_code }}</td>
                    <td class="font-medium text-erp-primary">{{ $customer->company_name }}</td>
                    <td class="hidden sm:table-cell">{{ $customer->branch?->name }}</td>
                    <td><x-admin.status-badge variant="info">{{ $customer->status->value }}</x-admin.status-badge></td>
                    <td class="text-right">
                        <a href="{{ route('admin.crm.customers.show', $customer) }}" class="font-medium text-erp-accent hover:underline">{{ __('View') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-admin.empty-state
                            icon="user-circle"
                            :title="__('No customers yet')"
                            :description="__('No quotations created yet — start by adding your first customer.')"
                        >
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
        <x-slot name="footer">{{ $customers->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
