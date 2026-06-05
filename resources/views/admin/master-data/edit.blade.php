<x-admin-layout
    :title="__('Edit Master Data Value')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Master Data'), 'url' => route('admin.master-data.index')],
        ['label' => $value->name],
    ]"
>
    <x-admin.page-header :title="__('Edit :name', ['name' => $value->name])" :description="__('Code :code is locked after creation.', ['code' => $value->code])" />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.master-data.update', $value) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.master-data.partials.form', [
                'value' => $value,
                'categories' => $categories,
                'showCategory' => false,
            ])
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                <span class="font-medium">{{ __('Category') }}:</span> {{ app(\App\Support\MasterData\MasterDataRegistry::class)->categoryLabel($value->category_key) }}
                · <span class="font-medium">{{ __('Code') }}:</span> <span class="font-mono">{{ $value->code }}</span>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('admin.master-data.index', ['category' => $value->category_key]) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
