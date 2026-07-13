<x-admin-layout :title="__('Create posting rule')" :breadcrumbs="[
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting rules'), 'url' => route('admin.accounting.posting.rules.index')],
    ['label' => __('Create')],
]">
    <x-admin.page-header :title="__('Create posting rule')" :description="__('Map a business event to a posting template.')" />

    <form method="POST" action="{{ route('admin.accounting.posting.rules.store') }}" class="space-y-4">
        @csrf
        @include('admin.accounting.posting.rules.partials.form', ['rule' => null])
        <div class="flex gap-2">
            <button class="erp-btn-primary" type="submit">{{ __('Create rule') }}</button>
            <a href="{{ route('admin.accounting.posting.rules.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
