@php
    $segment = $segment ?? null;
    $fields = $formFields ?? [];
@endphp

<div class="erp-form-grid">
    @if (auth()->user()->hasRole('Super Admin') && ! $segment)
        <x-admin.form-field name="company_id" :label="__('Company')" :required="true">
            <select name="company_id" class="erp-select w-full" required>
                @foreach ($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
    @else
        <input type="hidden" name="company_id" value="{{ $segment?->company_id ?? auth()->user()->company_id }}">
    @endif

    <x-admin.input
        name="name"
        :label="$fields['name']['label'] ?? __('Name')"
        :value="old('name', $segment?->name)"
        :required="($fields['name']['required'] ?? true)"
        :visible="($fields['name']['visible'] ?? true)"
        :readonly="($fields['name']['read_only'] ?? false)"
    />

    <x-admin.input
        name="code"
        :label="$fields['code']['label'] ?? __('Code')"
        :value="old('code', $segment?->code)"
        :required="($fields['code']['required'] ?? true)"
        :visible="($fields['code']['visible'] ?? true)"
        :readonly="($fields['code']['read_only'] ?? false)"
    />

    <x-admin.textarea
        name="description"
        :label="$fields['description']['label'] ?? __('Description')"
        :value="old('description', $segment?->description)"
        :required="($fields['description']['required'] ?? false)"
        :visible="($fields['description']['visible'] ?? true)"
        :readonly="($fields['description']['read_only'] ?? false)"
    />

    @if (($fields['is_active']['visible'] ?? true))
        <x-admin.form-field
            name="is_active"
            :label="$fields['is_active']['label'] ?? __('Active')"
            :visible="true"
            :readonly="($fields['is_active']['read_only'] ?? false)"
        >
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $segment?->is_active ?? true))
                    @disabled($fields['is_active']['read_only'] ?? false)
                >
                <span>{{ __('Active segment') }}</span>
            </label>
        </x-admin.form-field>
    @endif

    @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $segment ?? null])
</div>
