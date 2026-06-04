<x-admin-layout :title="__('Edit account')" :breadcrumbs="[['label' => __('Chart of Accounts'), 'url' => route('admin.accounting.accounts.index')], ['label' => $account->code]]">
    <x-admin.page-header :title="__('Edit :name', ['name' => $account->name])" :description="$account->code" />

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.accounting.accounts.update', $account) }}">
            @csrf
            @method('PUT')
            @include('admin.accounting.accounts.partials.form', ['account' => $account])
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                <a href="{{ route('admin.accounting.accounts.show', $account) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
