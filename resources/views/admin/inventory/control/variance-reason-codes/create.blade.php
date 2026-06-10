<x-admin-layout :title="__('Create variance reason code')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Variance reason codes'), 'url' => route('admin.inventory.variance-reason-codes.index')],
    ['label' => __('Create')],
]">
    <x-admin.page-header :title="__('Create variance reason code')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.variance-reason-codes.store') }}" class="max-w-xl space-y-4">
            @csrf
            @include('admin.inventory.control.variance-reason-codes.partials.form-fields', ['code' => null])
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.inventory.variance-reason-codes.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
