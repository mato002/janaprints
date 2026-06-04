@props(['customer', 'emailTimeline'])

@can('viewAny', App\Models\Communications\EmailCampaign::class)
    <div class="crm-360__channel-card">
        <div class="crm-360__card-head">
            <h3 class="erp-card-title">{{ __('Email') }}</h3>
            @can('create', App\Models\Communications\EmailCampaign::class)
                <x-admin.crm-btn
                    variant="outline"
                    size="sm"
                    :href="route('admin.communications.email.compose', ['to' => $customer->email, 'customer_id' => $customer->id])"
                    data-turbo-frame="erp-main"
                >
                    <x-slot:icon>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    {{ __('Compose') }}
                </x-admin.crm-btn>
            @endcan
        </div>
        @can('viewAny', App\Models\Communications\CommunicationLog::class)
            <div class="mt-3">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-2">{{ __('Email timeline (COM-4)') }}</p>
                <x-admin.communication-timeline :logs="$emailTimeline" :compact="true" />
            </div>
        @endcan
    </div>
@endcan
