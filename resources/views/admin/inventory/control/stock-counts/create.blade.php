@php($fields = $formFields ?? [])
<x-admin.modal-form
    :title="__('New stock count')"
    :breadcrumbs="[['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.inventory.stock-counts.store')">
        @if ($fromStoreDesk ?? request('from') === 'store-desk')
            <input type="hidden" name="from" value="store-desk">
        @endif
        <div class="erp-form-grid">
            @if (($fields['warehouse_id']['visible'] ?? true))
                <x-admin.form-field
                    name="warehouse_id"
                    :label="$fields['warehouse_id']['label'] ?? __('Warehouse')"
                    :required="($fields['warehouse_id']['required'] ?? true)"
                    :readonly="($fields['warehouse_id']['read_only'] ?? false)"
                >
                    <select name="warehouse_id" class="erp-select w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                        <option value="">{{ __('Select warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['count_type']['visible'] ?? true))
                <x-admin.form-field
                    name="count_type"
                    :label="$fields['count_type']['label'] ?? __('Count type')"
                    :required="($fields['count_type']['required'] ?? true)"
                    :readonly="($fields['count_type']['read_only'] ?? false)"
                >
                    <select name="count_type" class="erp-select w-full" @required($fields['count_type']['required'] ?? true) @disabled($fields['count_type']['read_only'] ?? false)>
                        @foreach ($countTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('count_type') === $type->value)>{{ ucfirst($type->value) }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['count_date']['visible'] ?? true))
                <x-admin.input
                    name="count_date"
                    type="date"
                    :label="$fields['count_date']['label'] ?? __('Count date')"
                    :value="old('count_date', now()->toDateString())"
                    :required="($fields['count_date']['required'] ?? true)"
                    :readonly="($fields['count_date']['read_only'] ?? false)"
                />
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

        <div id="partial-items" class="hidden mt-4">
            <x-admin.form-field name="item_ids" :label="__('Items (partial count)')" :colSpan="2">
                <select name="item_ids[]" class="erp-select w-full" multiple size="8">
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </x-admin.form-field>
        </div>

        @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create count') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>

    <script>
        document.querySelector('[name=count_type]')?.addEventListener('change', function () {
            document.getElementById('partial-items')?.classList.toggle('hidden', this.value !== 'partial');
        });
    </script>
</x-admin.modal-form>
