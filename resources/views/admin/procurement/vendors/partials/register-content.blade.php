@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Vendors')" :description="__('Supplier and vendor master data.')">
        <x-slot name="actions">
            @can('create', App\Models\Procurement\Vendor::class)
                <x-admin.form-modal-link :href="route('admin.procurement.vendors.create')">{{ __('Create vendor') }}</x-admin.form-modal-link>
            @endcan
        </x-slot>
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Vendors') }}</h2>
            @if (! empty($registerDescription))
                <p class="text-xs text-slate-600">{{ $registerDescription }}</p>
            @endif
        </div>
        @can('create', App\Models\Procurement\Vendor::class)
            <x-admin.form-modal-link :href="route('admin.procurement.vendors.create')">{{ __('Create vendor') }}</x-admin.form-modal-link>
        @endcan
    </div>
@endif

<x-admin.data-table
    :search-placeholder="__('Search vendors…')"
    export-route="admin.procurement.exports"
    :export-route-params="['listing' => 'vendors']"
    :export-query="request()->query()"
    :format-in-path="true"
    export-filename="vendors"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Vendor') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Type') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($vendors as $vendor)
            <tr x-show="rowVisible(@js(strtolower($vendor->vendor_code.' '.$vendor->vendor_name.' '.$vendor->vendor_type->value.' '.$vendor->status->value)))">
                <td>
                    <div class="font-medium">{{ $vendor->vendor_name }}</div>
                    <div class="font-mono text-[11px] text-slate-500">{{ $vendor->vendor_code }}</div>
                </td>
                <td class="hidden md:table-cell">{{ str($vendor->vendor_type->value)->headline() }}</td>
                <td><x-admin.enum-status-badge :status="$vendor->status->value" /></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.procurement.vendors.show', $vendor)">{{ __('View') }}</x-admin.table-row-action>
                        @can('update', $vendor)
                            <x-admin.table-row-action :href="route('admin.procurement.vendors.edit', $vendor)" data-erp-modal-open>{{ __('Edit') }}</x-admin.table-row-action>
                        @endcan
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><x-admin.empty-state icon="truck" :title="__('No vendors yet')" /></td></tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$vendors" /></x-slot>
</x-admin.data-table>
