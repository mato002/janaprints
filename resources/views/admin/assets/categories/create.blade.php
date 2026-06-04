<x-admin-layout :title="__('New asset category')" :breadcrumbs="[['label' => __('Categories'), 'url' => route('admin.assets.categories.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New asset category')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.categories.store') }}" class="max-w-md space-y-4">
            @csrf
            <div><x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="mt-1 w-full" required /></div>
            <div><x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="mt-1 w-full" /></div>
            <div><x-input-label for="default_gl_code" :value="__('Default GL code')" /><x-text-input id="default_gl_code" name="default_gl_code" class="mt-1 w-full" placeholder="1520" /></div>
            <div><x-input-label for="useful_life_months" :value="__('Useful life (months)')" /><x-text-input id="useful_life_months" name="useful_life_months" type="number" class="mt-1 w-full" value="60" required /></div>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
