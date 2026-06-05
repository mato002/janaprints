<x-admin-layout
    :title="__('Create Master Data Value')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Master Data'), 'url' => route('admin.master-data.index')],
        ['label' => __('Create')],
    ]"
>
    <x-admin.page-header :title="__('Create master data value')" :description="__('Add a reusable lookup value to the centralized reference catalog.')" />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.master-data.store') }}" class="space-y-4">
            @csrf
            @include('admin.master-data.partials.form', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
            ])
            <div class="flex gap-2 pt-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save value') }}</button>
                <a href="{{ route('admin.master-data.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
