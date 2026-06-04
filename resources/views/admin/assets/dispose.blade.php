<x-admin-layout :title="__('Dispose asset')" :breadcrumbs="[['label' => $asset->asset_number, 'url' => route('admin.assets.show', $asset)], ['label' => __('Dispose')]]">
    <x-admin.page-header :title="__('Dispose asset')" :description="$asset->asset_name" />
    <x-admin.card>
        <p class="mb-4 text-sm text-slate-600">{{ __('Net book value') }}: <strong>{{ number_format($asset->netBookValue(), 2) }}</strong></p>
        <form method="POST" action="{{ route('admin.assets.dispose.store', $asset) }}" class="max-w-md space-y-4">
            @csrf
            <div>
                <x-input-label for="disposal_date" :value="__('Disposal date')" />
                <x-text-input id="disposal_date" name="disposal_date" type="date" class="mt-1 w-full" required value="{{ now()->toDateString() }}" />
            </div>
            <div>
                <x-input-label for="disposal_proceeds" :value="__('Proceeds')" />
                <x-text-input id="disposal_proceeds" name="disposal_proceeds" type="number" step="0.01" class="mt-1 w-full" />
            </div>
            <div>
                <x-input-label for="disposal_method" :value="__('Method')" />
                <x-text-input id="disposal_method" name="disposal_method" class="mt-1 w-full" placeholder="sale, scrap, donation" />
            </div>
            <x-primary-button>{{ __('Confirm disposal') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
