@php($branch = $branch ?? null)

<div class="erp-form-grid">
    @if (auth()->user()->hasRole('Super Admin'))
        <x-admin.form-field name="company_id" :label="__('Company')" :required="true" :colSpan="2">
            <select name="company_id" id="company_id" class="erp-select w-full" required>
                @foreach ($companies as $c)
                    <option value="{{ $c->id }}" @selected(old('company_id', $branch?->company_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
    @else
        <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
    @endif

    <x-admin.input
        name="name"
        :label="__('Name')"
        :value="old('name', $branch?->name)"
        :required="true"
    />

    <x-admin.input
        name="code"
        :label="__('Code')"
        :value="old('code', $branch?->code)"
        :required="true"
    />

    <x-admin.form-field name="is_head_office" :label="__('Head office')" :colSpan="2">
        <input type="hidden" name="is_head_office" value="0">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_head_office" value="1" @checked(old('is_head_office', $branch?->is_head_office))>
            <span>{{ __('Head office branch') }}</span>
        </label>
    </x-admin.form-field>

    <x-admin.form-field name="is_active" :label="__('Active')" :colSpan="2">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch?->is_active ?? true))>
            <span>{{ __('Active branch') }}</span>
        </label>
    </x-admin.form-field>
</div>
