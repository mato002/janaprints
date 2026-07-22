<x-admin.modal-form
    :title="__('Add machine')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Machines'), 'url' => route('admin.assets.machines.index')],
        ['label' => __('Add')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.assets.machines.store')">
        <div class="space-y-6">
            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Machine details') }}</h3>
                <div class="erp-form-grid">
                    <x-admin.input
                        name="asset_name"
                        :label="__('Machine name')"
                        :required="true"
                        :value="old('asset_name')"
                        placeholder="{{ __('Heidelberg SM 74, Roland Versa…') }}"
                        class="md:col-span-2"
                    />

                    <x-admin.input
                        name="machine_code"
                        :label="__('Machine code')"
                        :required="true"
                        :value="old('machine_code')"
                        maxlength="50"
                        placeholder="{{ __('DIG-01, OFF-02…') }}"
                    />

                    <x-admin.input
                        name="machine_type"
                        :label="__('Machine type')"
                        :required="true"
                        :value="old('machine_type')"
                        maxlength="50"
                        placeholder="{{ __('Offset Press, Digital Press…') }}"
                    />

                    <x-admin.select name="asset_category_id" :label="__('Asset category')" :required="true">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('asset_category_id', $defaultCategoryId) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-admin.select>

                    <x-admin.select name="branch_id" :label="__('Branch')">
                        <option value="">{{ __('Default branch') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </x-admin.select>

                    <x-admin.input
                        name="manufacturer"
                        :label="__('Manufacturer')"
                        :value="old('manufacturer')"
                        maxlength="120"
                    />

                    <x-admin.input
                        name="model"
                        :label="__('Model')"
                        :value="old('model')"
                        maxlength="120"
                    />

                    <x-admin.input
                        name="serial_number"
                        :label="__('Serial number')"
                        :value="old('serial_number')"
                        maxlength="100"
                    />

                    <x-admin.input
                        name="production_area"
                        :label="__('Production area')"
                        :value="old('production_area')"
                        maxlength="120"
                        placeholder="{{ __('Press hall, Finishing…') }}"
                    />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Capacity') }}</h3>
                <div class="erp-form-grid">
                    <x-admin.input
                        name="shift_capacity"
                        type="number"
                        step="0.01"
                        min="0"
                        :label="__('Shift capacity')"
                        :value="old('shift_capacity', 10)"
                    />

                    <x-admin.input
                        name="hourly_capacity"
                        type="number"
                        step="0.01"
                        min="0"
                        :label="__('Hourly capacity')"
                        :value="old('hourly_capacity', 2)"
                    />
                </div>
            </div>

            <details class="rounded border border-erp-border p-3">
                <summary class="cursor-pointer text-sm font-medium text-slate-700">{{ __('Asset register details (optional)') }}</summary>
                <p class="mt-2 mb-3 text-xs text-slate-500">{{ __('These fields are stored on the underlying fixed asset record for finance and depreciation.') }}</p>
                <div class="erp-form-grid">
                    <x-admin.input
                        name="acquisition_date"
                        type="date"
                        :label="__('Acquisition date')"
                        :value="old('acquisition_date', now()->toDateString())"
                    />

                    <x-admin.input
                        name="acquisition_cost"
                        type="number"
                        step="0.01"
                        min="0"
                        :label="__('Acquisition cost')"
                        :value="old('acquisition_cost', 0)"
                    />

                    <x-admin.textarea
                        name="notes"
                        :label="__('Notes')"
                        :value="old('notes')"
                        class="md:col-span-2"
                    />
                </div>
            </details>
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Add machine') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
