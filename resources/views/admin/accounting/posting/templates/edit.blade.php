@php
    $oldLines = old('lines', $template->lines->map(fn ($line) => [
        'entry_side' => $line->entry_side->value,
        'account_resolver' => $line->account_resolver->value,
        'gl_account_id' => $line->gl_account_id,
        'account_key' => $line->account_key,
        'context_account_field' => $line->context_account_field,
        'amount_source' => $line->amount_source->value,
        'amount_field' => $line->amount_field,
        'line_description' => $line->line_description,
    ])->all());
@endphp

<x-admin-layout :title="__('Edit posting template')" :breadcrumbs="[
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting templates'), 'url' => route('admin.accounting.posting.templates.index')],
    ['label' => $template->code],
]">
    <x-admin.page-header :title="__('Edit :name', ['name' => $template->name])" />

    <form method="POST" action="{{ route('admin.accounting.posting.templates.update', $template) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.accounting.posting.templates.partials.form', ['template' => $template, 'oldLines' => $oldLines])
        <div class="flex gap-2">
            <button class="erp-btn-primary" type="submit">{{ __('Save template') }}</button>
            <a href="{{ route('admin.accounting.posting.templates.show', $template) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
