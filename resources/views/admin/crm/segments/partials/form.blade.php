@php
    $segment = $segment ?? null;
    $fields = $formFields ?? [];
@endphp

@if (auth()->user()->hasRole('Super Admin') && ! $segment)
    <div class="mb-4">
        <x-input-label for="company_id" :value="__('Company')" />
        <select name="company_id" class="block mt-1 w-full rounded-md border-gray-300" required>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
@else
    <input type="hidden" name="company_id" value="{{ $segment?->company_id ?? auth()->user()->company_id }}">
@endif

@if (($fields['name']['visible'] ?? true))
    <x-input-label for="name" :value="$fields['name']['label'] ?? __('Name')" />
    <x-text-input id="name" name="name" class="block mt-1 w-full mb-3" :value="old('name', $segment?->name)" :required="($fields['name']['required'] ?? true)" :readonly="($fields['name']['read_only'] ?? false)" />
@endif

@if (($fields['code']['visible'] ?? true))
    <x-input-label for="code" :value="$fields['code']['label'] ?? __('Code')" />
    <x-text-input id="code" name="code" class="block mt-1 w-full mb-3" :value="old('code', $segment?->code)" :required="($fields['code']['required'] ?? true)" :readonly="($fields['code']['read_only'] ?? false)" />
@endif

@if (($fields['description']['visible'] ?? true))
    <x-input-label for="description" :value="$fields['description']['label'] ?? __('Description')" />
    <textarea id="description" name="description" class="erp-input mt-1 w-full mb-3" rows="3" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $segment?->description) }}</textarea>
@endif

@if (($fields['is_active']['visible'] ?? true))
    <label class="inline-flex items-center gap-2 text-sm mb-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $segment?->is_active ?? true)) @disabled($fields['is_active']['read_only'] ?? false)>
        {{ $fields['is_active']['label'] ?? __('Active') }}
    </label>
@endif

@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $segment ?? null])
