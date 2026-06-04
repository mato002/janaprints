<x-admin-layout :title="__('Edit Tax Code')" :breadcrumbs="[['label' => __('Tax Codes'), 'url' => route('admin.tax.codes.index')], ['label' => $taxCode->code, 'url' => route('admin.tax.codes.show', $taxCode)], ['label' => __('Edit')]]">
    <x-admin.page-header :title="__('Edit :code', ['code' => $taxCode->code])" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.tax.codes.update', $taxCode) }}" class="max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-[11px] text-slate-500">{{ __('Category') }}</label>
                <select name="tax_category_id" class="erp-input mt-1 w-full" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('tax_category_id', $taxCode->tax_category_id) == $category->id)>{{ $category->code }} — {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[11px] text-slate-500">{{ __('Code') }}</label><input name="code" value="{{ old('code', $taxCode->code) }}" class="erp-input mt-1 w-full" required></div>
                <div><label class="text-[11px] text-slate-500">{{ __('Name') }}</label><input name="name" value="{{ old('name', $taxCode->name) }}" class="erp-input mt-1 w-full" required></div>
            </div>
            <div><label class="text-[11px] text-slate-500">{{ __('Description') }}</label><textarea name="description" class="erp-input mt-1 w-full" rows="2">{{ old('description', $taxCode->description) }}</textarea></div>
            <label class="flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $taxCode->is_active))>{{ __('Active') }}</label>
            <button class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
