<x-admin-layout :title="__('Leads')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Leads')]]">
    <x-admin.page-header :title="__('Leads')" :description="__('Sales opportunities and pipeline.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\Lead::class)
                <a href="{{ route('admin.crm.leads.create') }}" class="erp-btn-primary">{{ __('Create lead') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search leads…')"
        export-filename="leads"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'open', 'label' => __('Open')],
            ['id' => 'won', 'label' => __('Won')],
            ['id' => 'lost', 'label' => __('Lost')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Lead') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Stage') }}</th>
                <th scope="col">{{ __('Value') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($leads as $lead)
                @php
                    $search = strtolower($lead->lead_name.' '.($lead->stage?->name ?? '').' '.$lead->status->value);
                    $chip = strtolower($lead->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search), @js($chip))">
                    <td class="font-medium text-erp-primary">{{ $lead->lead_name }}</td>
                    <td class="hidden md:table-cell">{{ $lead->stage?->name ?? '—' }}</td>
                    <td class="tabular-nums">{{ number_format($lead->estimated_value, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$lead->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.crm.leads.show', $lead)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $lead)
                                <x-admin.table-row-action :href="route('admin.crm.leads.edit', $lead)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('delete', $lead)
                                <x-admin.table-row-action
                                    :action="route('admin.crm.leads.destroy', $lead)"
                                    method="DELETE"
                                    :confirm="__('Delete this lead?')"
                                >{{ __('Remove') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-admin.empty-state icon="sparkles" :title="__('No leads yet')" :description="__('Track opportunities from first contact to conversion.')">
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
        <x-slot name="footer"><x-admin.table-pagination :paginator="$leads" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
