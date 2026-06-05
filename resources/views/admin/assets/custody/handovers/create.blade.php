<x-admin-layout :title="__('New Handover')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Handovers'), 'url' => route('admin.assets.custody.handovers.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('Create Handover Record')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.custody.handovers.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                <label class="erp-label">{{ __('From Employee') }}</label>
                <select name="from_employee_id" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('To Employee') }}</label>
                <select name="to_employee_id" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('From Branch') }}</label>
                <select name="from_branch_id" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('To Branch') }}</label>
                <select name="to_branch_id" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Handover Date') }}</label>
                <input type="date" name="handover_date" value="{{ now()->toDateString() }}" class="erp-input w-full" required>
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
                <label class="erp-label">{{ __('Condition Notes') }}</label>
                <textarea name="condition_notes" class="erp-input w-full" rows="2"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Remarks') }}</label>
                <textarea name="remarks" class="erp-input w-full" rows="2"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Create Handover') }}</button>
                <a href="{{ route('admin.assets.custody.handovers.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
