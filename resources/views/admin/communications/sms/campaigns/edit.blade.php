<x-admin-layout :title="__('Edit SMS Campaign')" :breadcrumbs="[['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => $campaign->name]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('Edit campaign')" />

    <form
        method="POST"
        action="{{ route('admin.communications.sms.campaigns.update', $campaign) }}"
        data-erp-form-action="{{ route('admin.communications.sms.campaigns.update', $campaign) }}"
        class="erp-card !p-4"
        data-turbo-frame="erp-main"
    >
        @csrf
        @method('PUT')
        @include('admin.communications.sms.campaigns._form', ['campaign' => $campaign])
        <div class="mt-3 flex flex-wrap gap-2 border-t border-erp-border pt-3">
            @if ($campaign->status->canQueue())
                @can('send', $campaign)
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
            @endif
            <button type="submit" name="intent" value="draft" class="erp-btn erp-btn--secondary">{{ __('Save draft') }}</button>
            <a href="{{ route('admin.communications.sms.campaigns.show', $campaign) }}" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
