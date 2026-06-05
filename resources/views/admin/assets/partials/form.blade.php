<x-admin-layout
    :title="$title"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register'), 'url' => route('admin.assets.index')],
        ['label' => $title],
    ]"
>
    <x-admin.page-header :title="$title" />
    <x-admin.card>
        <form method="POST" action="{{ $action }}" class="max-w-2xl space-y-4">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div>
                <label class="text-xs text-slate-600" for="asset_category_id">{{ __('Category') }}</label>
                <select id="asset_category_id" name="asset_category_id" class="erp-select mt-1 w-full" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('asset_category_id', $asset?->asset_category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="asset_name">{{ __('Asset Name') }}</label>
                <input id="asset_name" name="asset_name" class="erp-input mt-1 w-full" value="{{ old('asset_name', $asset?->asset_name) }}" required>
            </div>
            @if ($asset)
                <div>
                    <label class="text-xs text-slate-600">{{ __('Asset Number') }}</label>
                    <input class="erp-input mt-1 w-full bg-slate-50" value="{{ $asset->asset_number }}" readonly>
                </div>
            @endif
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-600" for="manufacturer">{{ __('Manufacturer') }}</label>
                    <input id="manufacturer" name="manufacturer" class="erp-input mt-1 w-full" value="{{ old('manufacturer', $asset?->manufacturer) }}">
                </div>
                <div>
                    <label class="text-xs text-slate-600" for="model">{{ __('Model') }}</label>
                    <input id="model" name="model" class="erp-input mt-1 w-full" value="{{ old('model', $asset?->model) }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-600" for="serial_number">{{ __('Serial Number') }}</label>
                    <input id="serial_number" name="serial_number" class="erp-input mt-1 w-full" value="{{ old('serial_number', $asset?->serial_number) }}">
                </div>
                <div>
                    <label class="text-xs text-slate-600" for="barcode">{{ __('Barcode') }}</label>
                    <input id="barcode" name="barcode" class="erp-input mt-1 w-full" value="{{ old('barcode', $asset?->barcode) }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-600" for="acquisition_date">{{ __('Acquisition Date') }}</label>
                    <input id="acquisition_date" type="date" name="acquisition_date" class="erp-input mt-1 w-full" value="{{ old('acquisition_date', $asset?->acquisition_date?->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="text-xs text-slate-600" for="branch_id">{{ __('Branch') }}</label>
                    <select id="branch_id" name="branch_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('Select branch…') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $asset?->branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-600" for="acquisition_cost">{{ __('Acquisition Cost') }}</label>
                    <input id="acquisition_cost" type="number" step="0.01" min="0" name="acquisition_cost" class="erp-input mt-1 w-full" value="{{ old('acquisition_cost', $asset?->acquisition_cost) }}" required>
                </div>
                <div>
                    <label class="text-xs text-slate-600" for="residual_value">{{ __('Residual Value') }}</label>
                    <input id="residual_value" type="number" step="0.01" min="0" name="residual_value" class="erp-input mt-1 w-full" value="{{ old('residual_value', $asset?->residual_value ?? 0) }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-600" for="status">{{ __('Status') }}</label>
                    <select id="status" name="status" class="erp-select mt-1 w-full">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $asset?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-600" for="assigned_to_user_id">{{ __('Assigned User') }}</label>
                    <select id="assigned_to_user_id" name="assigned_to_user_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_to_user_id', $asset?->assigned_to_user_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="notes">{{ __('Notes') }}</label>
                <textarea id="notes" name="notes" rows="3" class="erp-input mt-1 w-full">{{ old('notes', $asset?->notes) }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ $asset ? __('Save Changes') : __('Register Asset') }}</button>
                <a href="{{ $asset ? route('admin.assets.show', $asset) : route('admin.assets.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
