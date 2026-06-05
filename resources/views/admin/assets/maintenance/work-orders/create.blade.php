<x-admin-layout :title="__('New Work Order')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Work Orders'), 'url' => route('admin.assets.maintenance.work-orders.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New Maintenance Work Order')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2"><label class="erp-label">{{ __('Asset') }}</label><select name="fixed_asset_id" class="erp-select w-full" required><option value="">{{ __('Select asset…') }}</option>@foreach ($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->asset_name }} ({{ $asset->asset_number }})</option>@endforeach</select></div>
            <div><label class="erp-label">{{ __('Maintenance Type') }}</label><select name="maintenance_type" class="erp-select w-full" required>@foreach ($types as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div>
            <div><label class="erp-label">{{ __('Priority') }}</label><select name="priority" class="erp-select w-full" required>@foreach ($priorities as $priority)<option value="{{ $priority->value }}">{{ $priority->label() }}</option>@endforeach</select></div>
            <div><label class="erp-label">{{ __('Scheduled For') }}</label><input type="datetime-local" name="scheduled_for" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Vendor') }}</label><select name="vendor_id" class="erp-select w-full"><option value="">{{ __('None') }}</option>@foreach ($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3"></textarea></div>
            <div class="sm:col-span-2"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="create_incident" value="1"> {{ __('Create maintenance incident') }}</label></div>
            <div class="sm:col-span-2"><button type="submit" class="erp-btn-primary">{{ __('Create Work Order') }}</button></div>
        </form>
    </x-admin.card>
</x-admin-layout>
