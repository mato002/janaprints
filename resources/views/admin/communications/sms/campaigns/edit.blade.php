<x-admin-layout :title="__('Edit SMS Campaign')" :breadcrumbs="[['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => $campaign->name]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('Edit campaign')" />

    <form method="POST" action="{{ route('admin.communications.sms.campaigns.update', $campaign) }}" class="erp-card" data-turbo-frame="erp-main">
        @csrf
        @method('PUT')
        @include('admin.communications.sms.campaigns._form', ['campaign' => $campaign])
        <div class="mt-4 flex gap-2">
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Update') }}</button>
            <a href="{{ route('admin.communications.sms.campaigns.show', $campaign) }}" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
