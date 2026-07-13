<x-admin-layout :title="__('Edit posting rule')" :breadcrumbs="[
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting rules'), 'url' => route('admin.accounting.posting.rules.index')],
    ['label' => $rule->name],
]">
    <x-admin.page-header :title="__('Edit :name', ['name' => $rule->name])" />

    <form method="POST" action="{{ route('admin.accounting.posting.rules.update', $rule) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.accounting.posting.rules.partials.form', ['rule' => $rule])
        <div class="flex gap-2">
            <button class="erp-btn-primary" type="submit">{{ __('Save rule') }}</button>
            <a href="{{ route('admin.accounting.posting.rules.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
