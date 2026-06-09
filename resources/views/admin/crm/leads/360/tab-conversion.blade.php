<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Conversion history') }}</h2>
            @if ($lead->customer_id && $lead->customer)
                <x-admin.crm-btn variant="outline" size="sm" :href="route('admin.crm.customers.show', $lead->customer)" data-turbo-frame="erp-main">{{ __('Open customer 360') }}</x-admin.crm-btn>
            @elseif(auth()->user()?->can('convert', $lead))
                <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" class="inline">@csrf
                    <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Convert lead') }}</x-admin.crm-btn>
                </form>
            @endif
        </div>

        <ul class="crm-360__feed" role="list">
            @foreach ($conversionHistory as $entry)
                <li class="crm-360__feed-item">
                    <div class="crm-360__feed-head">
                        @if (! empty($entry['url']))
                            <a href="{{ $entry['url'] }}" class="crm-360__feed-title" data-turbo-frame="erp-main">{{ $entry['event'] }}</a>
                        @else
                            <span class="crm-360__feed-title">{{ $entry['event'] }}</span>
                        @endif
                        <time class="crm-360__feed-time">{{ $entry['at']?->format('d M Y H:i') }}</time>
                    </div>
                    @if ($entry['detail'])
                        <p class="crm-360__feed-meta">{{ $entry['detail'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    @if ($lead->status === App\Enums\LeadStatus::Open)
        <section class="crm-360__card">
            <h2 class="crm-360__card-title">{{ __('Next steps') }}</h2>
            <ul class="crm-360__mini-list" role="list">
                <li>{{ __('Log activities and schedule follow-ups to advance the opportunity') }}</li>
                <li>{{ __('Convert to customer when ready to quote') }}</li>
                <li>{{ __('Create quotations linked to this lead for full acquisition traceability') }}</li>
            </ul>
        </section>
    @endif
</div>
