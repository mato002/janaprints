@php($fields = $formFields ?? [])
<x-admin.modal-form
    :title="__('Create transfer')"
    :breadcrumbs="[['label' => __('Store Transfers'), 'url' => route('admin.inventory.transfers.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.transfers.store')">
        <div class="erp-form-grid">
            @if (($fields['warehouse_id']['visible'] ?? true))
                <x-admin.form-field
                    name="warehouse_id"
                    :label="$fields['warehouse_id']['label'] ?? __('From store')"
                    :required="($fields['warehouse_id']['required'] ?? true)"
                >
                    <select id="warehouse_id" name="warehouse_id" class="erp-select w-full" @required($fields['warehouse_id']['required'] ?? true)>
                        <option value="">{{ __('Select store') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['to_warehouse_id']['visible'] ?? true))
                <x-admin.form-field
                    name="to_warehouse_id"
                    :label="$fields['to_warehouse_id']['label'] ?? __('To store')"
                    :required="($fields['to_warehouse_id']['required'] ?? true)"
                >
                    <select id="to_warehouse_id" name="to_warehouse_id" class="erp-select w-full" @required($fields['to_warehouse_id']['required'] ?? true)>
                        <option value="">{{ __('Select store') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('to_warehouse_id') == $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            @endif

            @if (($fields['issue_date']['visible'] ?? true))
                <x-admin.input
                    name="issue_date"
                    type="date"
                    :label="$fields['issue_date']['label'] ?? __('Transfer date')"
                    :value="old('issue_date', now()->toDateString())"
                    :required="($fields['issue_date']['required'] ?? true)"
                />
            @endif

            @if (($fields['notes']['visible'] ?? true))
                <x-admin.textarea
                    name="notes"
                    :label="$fields['notes']['label'] ?? __('Notes')"
                    :value="old('notes')"
                    :required="($fields['notes']['required'] ?? false)"
                />
            @endif
        </div>

        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create transfer') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
