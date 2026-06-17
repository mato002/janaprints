@props(['customer', 'emailTimeline', 'customerEmailMessages' => collect()])

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

        @if ($customerEmailMessages->isNotEmpty())
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($customerEmailMessages->take(8) as $message)
                    <li class="rounded border border-erp-border px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium">{{ Str::limit($message['subject'], 60) }}</span>
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message['status_badge'] }}">{{ $message['status_label'] }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $message['type_label'] }} · {{ $message['sender'] ?? '—' }} · {{ $message['date_formatted'] }}</p>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'communications']) }}" class="mt-3 inline-flex text-sm text-erp-accent" data-turbo-frame="erp-main">{{ __('View all communications') }}</a>
        @endif

        @can('viewAny', App\Models\Communications\CommunicationLog::class)
            <div class="mt-3">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-2">{{ __('Email timeline (COM-4)') }}</p>
                <x-admin.communication-timeline :logs="$emailTimeline" :compact="true" />
            </div>
        @endcan
    </div>
@endcan
