<x-admin-layout :title="__('Send SMS')" :breadcrumbs="[['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => __('Send SMS')]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header
        :title="__('Send SMS')"
        :description="__('Write a message, pick recipients, then send or save a draft.')"
        class="!mb-3"
    />

    <form
        method="POST"
        action="{{ route('admin.communications.sms.campaigns.store') }}"
        data-erp-form-action="{{ route('admin.communications.sms.campaigns.store') }}"
        class="erp-card !p-4"
        data-turbo-frame="erp-main"
    >
        @csrf
        @include('admin.communications.sms.campaigns._form')
        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-erp-border pt-3">
            @can('create', App\Models\Communications\SmsCampaign::class)
                <button
                    type="submit"
                    name="intent"
                    value="send"
                    class="erp-btn erp-btn--primary"
                    onclick="return confirm(@js(__('Send this SMS to the selected recipients now?')))"
                >
                    {{ __('Send now') }}
                </button>
            @endcan
            <button type="submit" name="intent" value="draft" class="erp-btn erp-btn--secondary">{{ __('Save draft') }}</button>
            <a href="{{ route('admin.communications.sms.campaigns.index') }}" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
