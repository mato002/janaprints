<x-admin.modal-form
    :title="__('New maintenance plan')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Maintenance'), 'url' => route('admin.assets.maintenance.dashboard', ['tab' => 'plans'])],
        ['label' => __('New')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.assets.maintenance.plans.store')">
        <div class="erp-form-grid">
            <x-admin.select name="fixed_asset_id" :label="__('Asset')" :required="true" class="md:col-span-2">
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}" @selected(old('fixed_asset_id') == $asset->id)>
                        {{ $asset->asset_name }} ({{ $asset->asset_number }})
                    </option>
                @endforeach
            </x-admin.select>

            <x-admin.input name="plan_name" :label="__('Plan name')" :required="true" :value="old('plan_name')" class="md:col-span-2" />

            <x-admin.select name="frequency_type" :label="__('Frequency')" :required="true">
                @foreach ($frequencies as $frequency)
                    <option value="{{ $frequency->value }}" @selected(old('frequency_type') === $frequency->value)>{{ $frequency->label() }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.input name="frequency_value" type="number" min="1" :label="__('Frequency value')" :required="true" :value="old('frequency_value', 1)" />

            <x-admin.input name="next_due_date" type="date" :label="__('Next due date')" :value="old('next_due_date')" class="md:col-span-2" />

            <x-admin.textarea name="description" :label="__('Description')" :value="old('description')" class="md:col-span-2" rows="2" />
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create plan') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
