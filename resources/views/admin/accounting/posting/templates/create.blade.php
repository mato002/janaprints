@php
    $oldLines = old('lines', [['entry_side' => 'debit', 'account_resolver' => 'account_key', 'amount_source' => 'total_amount', 'line_description' => ':description'], ['entry_side' => 'credit', 'account_resolver' => 'account_key', 'amount_source' => 'total_amount', 'line_description' => ':description']]);
@endphp

<x-admin-layout :title="__('Create posting template')" :breadcrumbs="[
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting templates'), 'url' => route('admin.accounting.posting.templates.index')],
    ['label' => __('Create')],
]">
    <x-admin.page-header :title="__('Create posting template')" :description="__('Define reusable debit/credit lines for automated journals.')" />

    <form method="POST" action="{{ route('admin.accounting.posting.templates.store') }}" class="space-y-4">
        @csrf
        @include('admin.accounting.posting.templates.partials.form', ['template' => null, 'oldLines' => $oldLines])
        <div class="flex gap-2">
            <button class="erp-btn-primary" type="submit">{{ __('Create template') }}</button>
            <a href="{{ route('admin.accounting.posting.templates.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
