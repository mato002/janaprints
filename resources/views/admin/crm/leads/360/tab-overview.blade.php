<div class="crm-360__grid crm-360__grid--overview">
    @if (! empty($acquisition))
        <section class="crm-360__card crm-360__card--full">
            <h2 class="crm-360__card-title">{{ __('Acquisition intake') }}</h2>
            <dl class="crm-360__dl">
                <div><dt>{{ __('Origin') }}</dt><dd>{{ $acquisition['origin'] }}</dd></div>
                <div><dt>{{ __('Reference') }}</dt>
                    <dd>
                        @if ($acquisition['url'])
                            <a href="{{ $acquisition['url'] }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $acquisition['reference'] }}</a>
                        @else
                            {{ $acquisition['reference'] }}
                        @endif
                    </dd>
                </div>
                <div><dt>{{ __('Requested product') }}</dt><dd>{{ $acquisition['requested_product'] }}</dd></div>
                <div><dt>{{ __('Quantity') }}</dt><dd>{{ $acquisition['quantity'] ?: '—' }}</dd></div>
                <div><dt>{{ __('Budget') }}</dt><dd>{{ $acquisition['budget'] ? number_format((float) $acquisition['budget'], 2) : '—' }}</dd></div>
                <div><dt>{{ __('Deadline') }}</dt><dd>{{ $acquisition['deadline'] ?: '—' }}</dd></div>
            </dl>
            @if (! empty($acquisition['attachments']))
                <div class="mt-4">
                    <p class="text-sm font-medium text-slate-700">{{ __('Attachments') }}</p>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach ($acquisition['attachments'] as $attachment)
                            <li class="flex flex-wrap items-center gap-2">
                                <span>{{ $attachment['name'] }}</span>
                                @if (! empty($attachment['preview_url']))
                                    <a href="{{ $attachment['preview_url'] }}" class="text-erp-accent hover:underline" target="_blank" rel="noopener">{{ __('Preview') }}</a>
                                @endif
                                @if (! empty($attachment['download_url']))
                                    <a href="{{ $attachment['download_url'] }}" class="text-erp-accent hover:underline">{{ __('Download') }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Opportunity overview') }}</h2>
        <dl class="crm-360__dl">
            <div><dt>{{ __('Lead source') }}</dt><dd>{{ $lead->leadSource?->name ?? '—' }}</dd></div>
            <div><dt>{{ __('Stage') }}</dt><dd>{{ $lead->stage?->name ?? '—' }}</dd></div>
            <div><dt>{{ __('Status') }}</dt><dd>{{ str_replace('_', ' ', $lead->status->value) }}</dd></div>
            <div><dt>{{ __('Assigned user') }}</dt><dd>{{ $lead->assignee?->name ?? '—' }}</dd></div>
            <div><dt>{{ __('Estimated value') }}</dt><dd>{{ number_format((float) $lead->estimated_value, 2) }}</dd></div>
            <div><dt>{{ __('Probability') }}</dt><dd>{{ $lead->probability !== null ? $lead->probability.'%' : '—' }}</dd></div>
            <div><dt>{{ __('Expected close date') }}</dt><dd>{{ $lead->expected_close_date?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt>{{ __('Customer link') }}</dt>
                <dd>
                    @if ($lead->customer)
                        <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $lead->customer->company_name }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
        @can('update', $lead)
            <div class="mt-3">
                <x-admin.crm-btn variant="outline" size="sm" :href="route('admin.crm.leads.edit', $lead)" data-turbo-frame="erp-main">{{ __('Edit lead') }}</x-admin.crm-btn>
            </div>
        @endcan
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Contact details') }}</h2>
        <dl class="crm-360__dl">
            <div><dt>{{ __('Company') }}</dt><dd>{{ $lead->company_name ?: '—' }}</dd></div>
            <div><dt>{{ __('Contact') }}</dt><dd>{{ $lead->lead_name }}</dd></div>
            <div><dt>{{ __('Phone') }}</dt><dd>{{ $lead->phone ?: '—' }}</dd></div>
            <div><dt>{{ __('Email') }}</dt><dd>{{ $lead->email ?: '—' }}</dd></div>
        </dl>
        @if ($lead->notes)
            <p class="mt-3 text-sm text-slate-600"><span class="font-medium">{{ __('Notes') }}:</span> {{ $lead->notes }}</p>
        @endif
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Recent activity') }}</h2>
            <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('activities')">{{ __('View all') }}</x-admin.crm-btn>
        </div>
        <ul class="crm-360__mini-list" role="list">
            @forelse ($lead->activities->sortByDesc('activity_at')->take(5) as $activity)
                <li>
                    <span class="font-medium text-erp-primary">{{ $activity->subject }}</span>
                    <span class="block text-[11px] text-slate-500">{{ ucfirst(str_replace('_', ' ', $activity->activity_type->value)) }} · {{ $activity->activity_at?->diffForHumans() }}</span>
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No activities logged yet') }}</li>
            @endforelse
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Upcoming follow-ups') }}</h2>
            <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('follow-ups')">{{ __('Manage') }}</x-admin.crm-btn>
        </div>
        <ul class="crm-360__mini-list" role="list">
            @forelse ($followUps['scheduled']->take(5) as $followUp)
                <li>
                    <span class="font-medium text-erp-primary">{{ $followUp['scheduled_at']?->format('d M Y H:i') }}</span>
                    <span class="block text-[11px] text-slate-500">{{ $followUp['notes'] ?: __('Scheduled follow-up') }}</span>
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No scheduled follow-ups') }}</li>
            @endforelse
        </ul>
    </section>
</div>
