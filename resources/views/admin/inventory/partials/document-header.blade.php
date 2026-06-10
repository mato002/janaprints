@php($fields = $formFields ?? [])
<div class="erp-form-grid max-w-5xl">
    @if (($fields['warehouse_id']['visible'] ?? true))
<<<<<<< Updated upstream
        <x-admin.form-field
            name="warehouse_id"
            :label="$fields['warehouse_id']['label'] ?? __('Warehouse')"
            :required="($fields['warehouse_id']['required'] ?? true)"
            :readonly="($fields['warehouse_id']['read_only'] ?? false)"
        >
            <select name="warehouse_id" class="erp-select w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected((string) old('warehouse_id', $selectedWarehouseId ?? null) === (string) $w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
=======
        <x-admin.lookup-select
            name="warehouse_id"
            :label="$fields['warehouse_id']['label'] ?? __('Warehouse')"
            :options="$warehouses"
            :value="old('warehouse_id', $selectedWarehouseId ?? null)"
            :required="($fields['warehouse_id']['required'] ?? true)"
            :readonly="($fields['warehouse_id']['read_only'] ?? false)"
            create-route="admin.inventory.warehouses.quick-create"
            refresh-route="admin.lookups.warehouses"
            permission="inventory.create"
            :modal-title="__('Create warehouse')"
            option-label-key="name"
            select-class="erp-input w-full"
            :empty-option="false"
        />
>>>>>>> Stashed changes
    @endif

    @if ($type === 'receipt')
        @if (($fields['source']['visible'] ?? true))
            <x-admin.form-field
                name="source"
                :label="$fields['source']['label'] ?? __('Source')"
                :required="($fields['source']['required'] ?? true)"
            >
                <select name="source" class="erp-select w-full" @required($fields['source']['required'] ?? true)>
                    @foreach ($sources as $s)<option value="{{ $s->value }}">{{ $s->value }}</option>@endforeach
                </select>
            </x-admin.form-field>
        @endif
        @if (($fields['receipt_date']['visible'] ?? true))
            <x-admin.input
                name="receipt_date"
                type="date"
                :label="$fields['receipt_date']['label'] ?? __('Date')"
                :value="old('receipt_date', now()->toDateString())"
                :required="($fields['receipt_date']['required'] ?? true)"
            />
        @endif
    @elseif ($type === 'issue')
        @if (($fields['destination']['visible'] ?? true))
            <x-admin.form-field
                name="destination"
                :label="$fields['destination']['label'] ?? __('Destination')"
                :required="($fields['destination']['required'] ?? true)"
            >
                <select name="destination" class="erp-select w-full" @required($fields['destination']['required'] ?? true)>
                    @foreach ($destinations as $d)<option value="{{ $d->value }}" @selected(old('destination') === $d->value)>{{ $d->value }}</option>@endforeach
                </select>
            </x-admin.form-field>
        @endif
        @if (($fields['issue_date']['visible'] ?? true))
            <x-admin.input
                name="issue_date"
                type="date"
                :label="$fields['issue_date']['label'] ?? __('Date')"
                :value="old('issue_date', now()->toDateString())"
                :required="($fields['issue_date']['required'] ?? true)"
            />
        @endif
        @if (($fields['to_warehouse_id']['visible'] ?? true))
<<<<<<< Updated upstream
            <x-admin.form-field
                name="to_warehouse_id"
                :label="$fields['to_warehouse_id']['label'] ?? __('To warehouse (transfer)')"
                :required="($fields['to_warehouse_id']['required'] ?? false)"
            >
                <select name="to_warehouse_id" class="erp-select w-full" @required($fields['to_warehouse_id']['required'] ?? false)>
                    <option value="">{{ __('N/A') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string) old('to_warehouse_id') === (string) $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </x-admin.form-field>
=======
            <x-admin.lookup-select
                name="to_warehouse_id"
                :label="$fields['to_warehouse_id']['label'] ?? __('To warehouse (transfer)')"
                :options="$warehouses"
                :value="old('to_warehouse_id')"
                :required="($fields['to_warehouse_id']['required'] ?? false)"
                :readonly="($fields['to_warehouse_id']['read_only'] ?? false)"
                create-route="admin.inventory.warehouses.quick-create"
                refresh-route="admin.lookups.warehouses"
                permission="inventory.create"
                :modal-title="__('Create warehouse')"
                option-label-key="name"
                select-class="erp-input w-full"
                :placeholder="__('N/A')"
            />
>>>>>>> Stashed changes
        @endif
    @else
        @if (($fields['adjustment_date']['visible'] ?? true))
            <x-admin.input
                name="adjustment_date"
                type="date"
                :label="$fields['adjustment_date']['label'] ?? __('Date')"
                :value="old('adjustment_date', now()->toDateString())"
                :required="($fields['adjustment_date']['required'] ?? true)"
            />
        @endif
        @if (($fields['reason']['visible'] ?? true))
            <x-admin.input
                name="reason"
                :label="$fields['reason']['label'] ?? __('Reason')"
                :value="old('reason')"
                :required="($fields['reason']['required'] ?? true)"
                :colSpan="2"
            />
        @endif
    @endif
</div>

@if ($type === 'issue' && ($fields['notes']['visible'] ?? true))
    <div class="mt-4 max-w-5xl">
        <x-admin.textarea
            name="notes"
            :label="$fields['notes']['label'] ?? __('Notes')"
            :value="old('notes')"
            :required="($fields['notes']['required'] ?? false)"
            :readonly="($fields['notes']['read_only'] ?? false)"
        />
    </div>
@endif
<<<<<<< Updated upstream

=======
@if ($type === 'issue' && ! empty($productionGovernance))
    <div class="mt-4 max-w-3xl rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-semibold">{{ $productionGovernance['heading'] ?? __('Production consumption governance') }}</p>
        <p class="mt-1">{{ $productionGovernance['message'] ?? '' }}</p>
        <p class="mt-1 text-amber-800">{{ $productionGovernance['guidance'] ?? '' }}</p>
    </div>
    @if ($productionGovernance['production_destination_allowed'] ?? false)
        <div class="mt-4 max-w-3xl">
            <label class="erp-label">{{ __('Production override reason') }}</label>
            <textarea name="production_override_reason" class="erp-input w-full" rows="2">{{ old('production_override_reason') }}</textarea>
            @error('production_override_reason')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif
@endif
>>>>>>> Stashed changes
@include('admin.partials.form-custom-fields', ['fields' => $formFields ?? []])
