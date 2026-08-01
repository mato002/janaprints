@if (auth()->user()->hasRole('Super Admin'))
    <x-admin.lookup-company-select :companies="$companies" :value="old('company_id', $branch?->company_id)" select-class="block mt-1 w-full rounded-md border-gray-300" class="mb-4" />
@else
    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
@endif
<div class="grid gap-4">
    <div><x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $branch?->name)" required /></div>
    <x-admin.entity-code-input :record="$branch" />
    <label class="flex gap-2"><input type="hidden" name="is_head_office" value="0"><input type="checkbox" name="is_head_office" value="1" @checked(old('is_head_office', $branch?->is_head_office))> {{ __('Head office') }}</label>
    <label class="flex gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch?->is_active ?? true))> {{ __('Active') }}</label>
</div>
