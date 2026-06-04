<x-admin-layout :title="__('New account')" :breadcrumbs="[['label' => __('Chart of Accounts'), 'url' => route('admin.accounting.accounts.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New GL account')" />

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.accounting.accounts.store') }}">
            @csrf
            @include('admin.accounting.accounts.partials.form')
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Create account') }}</x-primary-button>
                <a href="{{ route('admin.accounting.accounts.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
