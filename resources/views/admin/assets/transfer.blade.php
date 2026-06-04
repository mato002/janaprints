<x-admin-layout :title="__('Transfer asset')" :breadcrumbs="[['label' => $asset->asset_number, 'url' => route('admin.assets.show', $asset)], ['label' => __('Transfer')]]">
    <x-admin.page-header :title="__('Transfer asset')" :description="$asset->asset_name" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.transfer.store', $asset) }}" class="max-w-md space-y-4">
            @csrf
            <div>
                <x-input-label for="to_branch_id" :value="__('To branch')" />
                <select id="to_branch_id" name="to_branch_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="transfer_date" :value="__('Transfer date')" />
                <x-text-input id="transfer_date" name="transfer_date" type="date" class="mt-1 w-full" required value="{{ now()->toDateString() }}" />
            </div>
            <x-primary-button>{{ __('Transfer') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
