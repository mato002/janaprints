<x-admin-layout :title="__('Leads')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Leads')]]">
    <x-admin.page-header :title="__('Leads')" :description="__('Sales opportunities and pipeline.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\Lead::class)
                <a href="{{ route('admin.crm.leads.create') }}" class="erp-btn-primary">{{ __('Create lead') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Lead') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Stage') }}</th>
                <th scope="col">{{ __('Value') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($leads as $lead)
                <tr x-show="matches(@js($lead->lead_name.' '.($lead->stage?->name ?? '').' '.$lead->status->value))">
                    <td class="font-medium text-erp-primary">{{ $lead->lead_name }}</td>
                    <td class="hidden md:table-cell">{{ $lead->stage?->name }}</td>
                    <td class="tabular-nums">{{ number_format($lead->estimated_value, 2) }}</td>
                    <td><x-admin.status-badge variant="neutral">{{ $lead->status->value }}</x-admin.status-badge></td>
                    <td class="text-right">
                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="font-medium text-erp-accent hover:underline">{{ __('View') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-admin.empty-state
                            icon="sparkles"
                            :title="__('No leads yet')"
                            :description="__('Track opportunities from first contact to conversion.')"
                        >
                            <x-slot name="action">
                                @can('create', App\Models\Crm\Lead::class)
                                    <a href="{{ route('admin.crm.leads.create') }}" class="erp-btn-primary">{{ __('Create lead') }}</a>
                                @endcan
                            </x-slot>
                        </x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $leads->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
