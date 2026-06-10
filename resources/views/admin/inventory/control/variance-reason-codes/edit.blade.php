<x-admin-layout :title="__('Edit variance reason code')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Variance reason codes'), 'url' => route('admin.inventory.variance-reason-codes.index')],
    ['label' => $code->code],
]">
    <x-admin.page-header :title="__('Edit variance reason code')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.variance-reason-codes.update', $code) }}" class="max-w-xl space-y-4">
            @csrf @method('PUT')
            @include('admin.inventory.control.variance-reason-codes.partials.form-fields', ['code' => $code])
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.inventory.variance-reason-codes.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
