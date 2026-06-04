<x-admin-layout :title="__('New Tax Code')" :breadcrumbs="[['label' => __('Tax Codes'), 'url' => route('admin.tax.codes.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Tax Code')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.tax.codes.store') }}" class="max-w-xl space-y-4">
            @csrf
            <div>
                <label class="text-[11px] text-slate-500">{{ __('Category') }}</label>
                <select name="tax_category_id" class="erp-input mt-1 w-full" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('tax_category_id') == $category->id)>{{ $category->code }} — {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[11px] text-slate-500">{{ __('Code') }}</label><input name="code" value="{{ old('code') }}" class="erp-input mt-1 w-full" required></div>
                <div><label class="text-[11px] text-slate-500">{{ __('Name') }}</label><input name="name" value="{{ old('name') }}" class="erp-input mt-1 w-full" required></div>
            </div>
            <div><label class="text-[11px] text-slate-500">{{ __('Description') }}</label><textarea name="description" class="erp-input mt-1 w-full" rows="2">{{ old('description') }}</textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[11px] text-slate-500">{{ __('Initial rate %') }}</label><input type="number" step="0.01" name="rate_percent" value="{{ old('rate_percent') }}" class="erp-input mt-1 w-full"></div>
                <div><label class="text-[11px] text-slate-500">{{ __('Effective from') }}</label><input type="date" name="effective_from" value="{{ old('effective_from', '2020-01-01') }}" class="erp-input mt-1 w-full"></div>
            </div>
            <button class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
