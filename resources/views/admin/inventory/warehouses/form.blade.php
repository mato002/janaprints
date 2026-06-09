@php($fields = $formFields ?? [])

<div class="erp-form-grid">
    @if (($fields['name']['visible'] ?? true))
        <div>
            <x-input-label for="name" :value="$fields['name']['label'] ?? __('Warehouse Name')" />
            <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $warehouse?->name)" :required="($fields['name']['required'] ?? true)" :readonly="($fields['name']['read_only'] ?? false)" />
        </div>
    @endif

    @if (($fields['code']['visible'] ?? true))
        <div>
            <x-input-label for="code" :value="$fields['code']['label'] ?? __('Warehouse Code')" />
            <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $warehouse?->code)" :required="($fields['code']['required'] ?? true)" :readonly="($fields['code']['read_only'] ?? false)" />
        </div>
    @endif

    @if (($fields['branch']['visible'] ?? true))
        <div>
            <x-input-label for="branch_id" :value="$fields['branch']['label'] ?? __('Branch')" />
            <select id="branch_id" name="branch_id" class="erp-select mt-1" @required($fields['branch']['required'] ?? false) @disabled($fields['branch']['read_only'] ?? false)>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $warehouse?->branch_id ?? $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (($fields['location']['visible'] ?? true))
        <div>
            <x-input-label for="location" :value="$fields['location']['label'] ?? __('Location')" />
            <x-text-input id="location" name="location" class="block mt-1 w-full" :value="old('location')" :required="($fields['location']['required'] ?? false)" :readonly="($fields['location']['read_only'] ?? false)" />
        </div>
    @endif

    @if (($fields['description']['visible'] ?? true))
        <div class="md:col-span-2">
            <x-input-label for="notes" :value="$fields['description']['label'] ?? __('Notes')" />
            <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="3" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('notes', $warehouse?->description) }}</textarea>
        </div>
    @endif

    @if (($fields['is_active']['visible'] ?? true))
        <div class="md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse?->is_active ?? true)) @disabled($fields['is_active']['read_only'] ?? false)>
                <span>{{ $fields['is_active']['label'] ?? __('Active store') }}</span>
            </label>
        </div>
    @endif
</div>
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $warehouse ?? null])
