<x-admin.modal-form
    :title="__('New work order')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Maintenance'), 'url' => route('admin.assets.maintenance.dashboard')],
        ['label' => __('New')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.assets.maintenance.work-orders.store')">
        <div class="erp-form-grid">
            <x-admin.select name="fixed_asset_id" :label="__('Asset')" :required="true" class="md:col-span-2">
                <option value="">{{ __('Select asset…') }}</option>
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}" @selected(old('fixed_asset_id') == $asset->id)>
                        {{ $asset->asset_name }} ({{ $asset->asset_number }})
                    </option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="maintenance_type" :label="__('Maintenance type')" :required="true">
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('maintenance_type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="priority" :label="__('Priority')" :required="true">
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.input name="scheduled_for" type="datetime-local" :label="__('Scheduled for')" :value="old('scheduled_for')" />

            <x-admin.select name="vendor_id" :label="__('Vendor')">
                <option value="">{{ __('None') }}</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->vendor_name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.textarea name="description" :label="__('Description')" :value="old('description')" class="md:col-span-2" rows="3" />

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="create_incident" value="1" @checked(old('create_incident'))>
                    {{ __('Create maintenance incident') }}
                </label>
            </div>
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create work order') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
