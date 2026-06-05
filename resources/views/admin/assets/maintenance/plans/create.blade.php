<x-admin-layout :title="__('New Plan')" :breadcrumbs="[['label' => __('Maintenance Plans'), 'url' => route('admin.assets.maintenance.plans.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New Maintenance Plan')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.maintenance.plans.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">@csrf
            <div class="sm:col-span-2"><label class="erp-label">{{ __('Asset') }}</label><select name="fixed_asset_id" class="erp-select w-full" required>@foreach ($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->asset_name }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label class="erp-label">{{ __('Plan Name') }}</label><input type="text" name="plan_name" class="erp-input w-full" required></div>
            <div><label class="erp-label">{{ __('Frequency') }}</label><select name="frequency_type" class="erp-select w-full" required>@foreach ($frequencies as $f)<option value="{{ $f->value }}">{{ $f->label() }}</option>@endforeach</select></div>
            <div><label class="erp-label">{{ __('Frequency Value') }}</label><input type="number" name="frequency_value" class="erp-input w-full" value="1" min="1" required></div>
            <div><label class="erp-label">{{ __('Next Due Date') }}</label><input type="date" name="next_due_date" class="erp-input w-full"></div>
            <div class="sm:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="2"></textarea></div>
            <div class="sm:col-span-2"><button type="submit" class="erp-btn-primary">{{ __('Create Plan') }}</button></div>
        </form>
    </x-admin.card>
</x-admin-layout>
