<x-admin-layout :title="__('New BOM')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.workspaces.production')], ['label' => __('Bills of Materials'), 'url' => route('admin.production.boms.index')], ['label' => __('New BOM')]]">
    <x-admin.page-header :title="__('New Bill of Materials')" />
    <form method="POST" action="{{ route('admin.production.boms.store') }}" class="space-y-4">
        @csrf
        @include('admin.production.boms._form')
        <div class="flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Create BOM') }}</button>
            <a href="{{ route('admin.production.boms.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
