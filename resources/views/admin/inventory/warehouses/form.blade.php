@php($fields = $formFields ?? [])

<div class="erp-form-grid">
    <div>
        <x-input-label for="name" :value="$fields['name']['label'] ?? __('Warehouse Name')" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $warehouse?->name)" required />
    </div>

    <div>
        <x-input-label for="code" :value="$fields['code']['label'] ?? __('Warehouse Code')" />
        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $warehouse?->code)" required />
    </div>

    <div>
        <x-input-label for="branch_id" :value="$fields['branch_id']['label'] ?? __('Branch')" />
        <select id="branch_id" name="branch_id" class="erp-select mt-1" required>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $warehouse?->branch_id ?? $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="location" :value="$fields['location']['label'] ?? __('Location')" />
        <x-text-input id="location" name="location" class="block mt-1 w-full" :value="old('location')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notes" :value="$fields['notes']['label'] ?? __('Notes')" />
        <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="3">{{ old('notes', $warehouse?->description) }}</textarea>
    </div>

    <div class="md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse?->is_active ?? true))>
            <span>{{ $fields['is_active']['label'] ?? __('Active store') }}</span>
        </label>
    </div>
</div>
