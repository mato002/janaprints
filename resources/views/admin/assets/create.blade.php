<x-admin-layout :title="__('Register asset')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.assets.dashboard')], ['label' => __('Register')]]">
    <x-admin.page-header :title="__('Register fixed asset')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.store') }}" class="space-y-4 max-w-xl">
            @csrf
            <div>
                <x-input-label for="asset_category_id" :value="__('Category')" />
                <select id="asset_category_id" name="asset_category_id" class="erp-select mt-1 w-full" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="asset_name" :value="__('Asset name')" />
                <x-text-input id="asset_name" name="asset_name" class="mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="acquisition_date" :value="__('Acquisition date')" />
                <x-text-input id="acquisition_date" name="acquisition_date" type="date" class="mt-1 w-full" required />
            </div>
            <div>
                <x-input-label for="acquisition_cost" :value="__('Acquisition cost')" />
                <x-text-input id="acquisition_cost" name="acquisition_cost" type="number" step="0.01" class="mt-1 w-full" required />
            </div>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
