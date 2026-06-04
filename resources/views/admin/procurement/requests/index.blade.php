<x-admin-layout :title="__('Purchase Requests')" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Purchase Requests')]]">
    <x-admin.page-header :title="__('Purchase Requests')">
        <x-slot name="actions">
            @can('create', App\Models\Procurement\PurchaseRequest::class)
                <a href="{{ route('admin.procurement.requests.create') }}" class="erp-btn-primary">{{ __('Create request') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search purchase requests…')" export-filename="purchase-requests">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Requester') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Required') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($requests as $request)
                <tr x-show="rowVisible(@js(strtolower($request->request_number.' '.($request->requester?->name ?? '').' '.$request->status->value)))">
                    <td class="font-mono text-xs">{{ $request->request_number }}</td>
                    <td>{{ $request->requester?->name ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $request->required_date?->format('Y-m-d') ?: '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$request->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.procurement.requests.show', $request)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $request)
                                <x-admin.table-row-action :href="route('admin.procurement.requests.edit', $request)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="clipboard-list" :title="__('No purchase requests')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$requests" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
