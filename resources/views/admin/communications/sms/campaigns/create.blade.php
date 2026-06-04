<x-admin-layout :title="__('Create SMS Campaign')" :breadcrumbs="[['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => __('Create')]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('Create SMS campaign')" />

    <form method="POST" action="{{ route('admin.communications.sms.campaigns.store') }}" class="erp-card" data-turbo-frame="erp-main">
        @csrf
        @include('admin.communications.sms.campaigns._form')
        <div class="mt-4 flex gap-2">
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Save draft') }}</button>
            <a href="{{ route('admin.communications.sms.campaigns.index') }}" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
