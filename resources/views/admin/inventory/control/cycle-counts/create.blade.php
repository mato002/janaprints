@php($fields = $formFields ?? [])
<x-admin.modal-form
    :title="__('New cycle count schedule')"
    :breadcrumbs="[['label' => __('Cycle Count'), 'url' => route('admin.inventory.cycle-counts.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.inventory.cycle-counts.store')">
        <div class="erp-form-grid">
            @if (($fields['warehouse_id']['visible'] ?? true))
                <x-admin.form-field
                    name="warehouse_id"
                    :label="$fields['warehouse_id']['label'] ?? __('Warehouse')"
                    :required="($fields['warehouse_id']['required'] ?? true)"
                    :readonly="($fields['warehouse_id']['read_only'] ?? false)"
                >
                    <select name="warehouse_id" class="erp-select w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['frequency']['visible'] ?? true))
                <x-admin.form-field
                    name="frequency"
                    :label="$fields['frequency']['label'] ?? __('Frequency')"
                    :required="($fields['frequency']['required'] ?? true)"
                    :readonly="($fields['frequency']['read_only'] ?? false)"
                >
                    <select name="frequency" class="erp-select w-full" @required($fields['frequency']['required'] ?? true) @disabled($fields['frequency']['read_only'] ?? false)>
                        @foreach ($frequencies as $freq)
                            <option value="{{ $freq->value }}" @selected(old('frequency') === $freq->value)>{{ ucfirst($freq->value) }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['next_count_date']['visible'] ?? true))
                <x-admin.input
                    name="next_count_date"
                    type="date"
                    :label="$fields['next_count_date']['label'] ?? __('Next count date')"
                    :value="old('next_count_date', now()->toDateString())"
                    :required="($fields['next_count_date']['required'] ?? true)"
                    :readonly="($fields['next_count_date']['read_only'] ?? false)"
                />
            @endif

            @if (($fields['inventory_category_id']['visible'] ?? true))
                <x-admin.form-field
                    name="inventory_category_id"
                    :label="$fields['inventory_category_id']['label'] ?? __('Category (optional)')"
                    :required="($fields['inventory_category_id']['required'] ?? false)"
                    :readonly="($fields['inventory_category_id']['read_only'] ?? false)"
                >
                    <select name="inventory_category_id" class="erp-select w-full" @required($fields['inventory_category_id']['required'] ?? false) @disabled($fields['inventory_category_id']['read_only'] ?? false)>
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('inventory_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['responsible_user_id']['visible'] ?? true))
                <x-admin.form-field
                    name="responsible_user_id"
                    :label="$fields['responsible_user_id']['label'] ?? __('Responsible user')"
                    :required="($fields['responsible_user_id']['required'] ?? true)"
                    :readonly="($fields['responsible_user_id']['read_only'] ?? false)"
                >
                    <select name="responsible_user_id" class="erp-select w-full" @required($fields['responsible_user_id']['required'] ?? true) @disabled($fields['responsible_user_id']['read_only'] ?? false)>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('responsible_user_id', auth()->id()) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['notes']['visible'] ?? true))
                <x-admin.textarea
                    name="notes"
                    :label="$fields['notes']['label'] ?? __('Notes')"
                    :value="old('notes')"
                    :required="($fields['notes']['required'] ?? false)"
                    :readonly="($fields['notes']['read_only'] ?? false)"
                />
            @endif
        </div>

        @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create schedule') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
