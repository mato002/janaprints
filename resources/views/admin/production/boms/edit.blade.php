<x-admin-layout :title="__('Edit BOM')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.workspaces.production')], ['label' => __('Bills of Materials'), 'url' => route('admin.production.boms.index')], ['label' => $bom->name]]">
    <x-admin.page-header :title="$bom->name">
        <x-slot name="subtitle">{{ $bom->finishedItem?->item_name }} ({{ $bom->finishedItem?->sku }})</x-slot>
    </x-admin.page-header>
    <form method="POST" action="{{ route('admin.production.boms.update', $bom) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.production.boms._form', ['bom' => $bom])
        <div class="flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save BOM') }}</button>
            <a href="{{ route('admin.production.boms.index') }}" class="erp-btn-secondary">{{ __('Back') }}</a>
        </div>
    </form>
</x-admin-layout>
