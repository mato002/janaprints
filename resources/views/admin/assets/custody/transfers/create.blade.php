<x-admin-layout :title="__('New Transfer')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Branch Transfers'), 'url' => route('admin.assets.custody.transfers.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('Request Branch Transfer')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.custody.transfers.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Asset') }}</label>
                <select name="fixed_asset_id" class="erp-select w-full" required>
                    <option value="">{{ __('Select asset…') }}</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('From Branch') }}</label>
                <select name="from_branch_id" class="erp-select w-full">
                    <option value="">{{ __('Current branch') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('To Branch') }}</label>
                <select name="to_branch_id" class="erp-select w-full" required>
                    <option value="">{{ __('Select branch…') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Condition') }}</label>
                <select name="condition" class="erp-select w-full">
                    @foreach ($conditions as $condition)
                        <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Transfer Reason') }}</label>
                <textarea name="transfer_reason" class="erp-input w-full" rows="2"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Request Transfer') }}</button>
                <a href="{{ route('admin.assets.custody.transfers.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
