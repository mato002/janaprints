@php($fields = $formFields ?? [])

<div class="erp-form-grid">
    @if (($fields['name']['visible'] ?? true))
        <x-admin.input
            name="name"
            :label="$fields['name']['label'] ?? __('Warehouse name')"
            :value="old('name', $warehouse?->name)"
            :required="($fields['name']['required'] ?? true)"
            :readonly="($fields['name']['read_only'] ?? false)"
        />
    @endif

    @if (($fields['code']['visible'] ?? true))
        <x-admin.input
            name="code"
            :label="$fields['code']['label'] ?? __('Warehouse code')"
            :value="old('code', $warehouse?->code)"
            :required="($fields['code']['required'] ?? true)"
            :readonly="($fields['code']['read_only'] ?? false)"
        />
    @endif

    @if (($fields['branch_id']['visible'] ?? false))
        <x-admin.form-field
            name="branch_id"
            :label="$fields['branch_id']['label'] ?? __('Branch')"
            :required="($fields['branch_id']['required'] ?? false)"
            :readonly="($fields['branch_id']['read_only'] ?? false)"
        >
            <select id="branch_id" name="branch_id" class="erp-select w-full" @required($fields['branch_id']['required'] ?? false) @disabled($fields['branch_id']['read_only'] ?? false)>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $warehouse?->branch_id ?? $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
    @endif

    @if (($fields['location']['visible'] ?? true))
        <x-admin.input
            name="location"
            :label="$fields['location']['label'] ?? __('Location')"
            :value="old('location', $warehouse?->location)"
            :required="($fields['location']['required'] ?? false)"
            :readonly="($fields['location']['read_only'] ?? false)"
        />
    @endif

    @if (($fields['description']['visible'] ?? true) || ($fields['notes']['visible'] ?? true))
        <x-admin.textarea
            name="notes"
            :label="$fields['notes']['label'] ?? $fields['description']['label'] ?? __('Notes')"
            :value="old('notes', $warehouse?->description)"
            :required="($fields['notes']['required'] ?? $fields['description']['required'] ?? false)"
            :readonly="($fields['notes']['read_only'] ?? $fields['description']['read_only'] ?? false)"
            :colSpan="2"
        />
    @endif

    @if (($fields['is_active']['visible'] ?? true))
        <x-admin.form-field
            name="is_active"
            :label="$fields['is_active']['label'] ?? __('Active store')"
            :readonly="($fields['is_active']['read_only'] ?? false)"
            :colSpan="2"
        >
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse?->is_active ?? true)) @disabled($fields['is_active']['read_only'] ?? false)>
                <span>{{ __('Active store') }}</span>
            </label>
        </x-admin.form-field>
    @endif
</div>
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $warehouse ?? null])
