<x-admin-layout :title="$lead->lead_name" :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => $lead->lead_name]]">
    <div class="lead-360">
        <header class="lead-360__header">
            <div class="lead-360__identity">
                <h1 class="lead-360__title">{{ $lead->lead_name }}</h1>
                @if ($lead->company_name)
                    <p class="lead-360__subtitle">{{ $lead->company_name }}</p>
                @endif
                <div class="lead-360__meta">
                    <x-admin.status-badge variant="info">{{ $lead->status->value }}</x-admin.status-badge>
                    <span class="lead-360__meta-item">{{ $lead->stage?->name ?? __('No stage') }}</span>
                    <span class="lead-360__meta-item">{{ __('Value') }}: {{ number_format((float) $lead->estimated_value, 2) }}</span>
                </div>
            </div>

            <div class="lead-360__actions">
                @can('quote', $lead)
                    <a href="{{ route('admin.crm.leads.quotation.create', $lead) }}" class="crm-360__btn crm-360__btn--primary" data-turbo-frame="erp-main">
                        {{ __('Create Quotation') }}
                    </a>
                    <form method="POST" action="{{ route('admin.crm.leads.quotation.quick', $lead) }}" class="inline">
                        @csrf
                        <button type="submit" class="crm-360__btn crm-360__btn--outline">{{ __('Quick Quote') }}</button>
                    </form>
                @endcan

                @if ($lead->customer_id && $lead->customer)
                    <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">{{ __('Open Customer') }}</a>
                @elseif(auth()->user()?->can('convert', $lead))
                    <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" class="inline">@csrf
                        <button type="submit" class="crm-360__btn crm-360__btn--outline">{{ __('Convert to Customer') }}</button>
                    </form>
                @endif

                @can('update', $lead)
                    <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="crm-360__btn crm-360__btn--ghost" data-turbo-frame="erp-main">{{ __('Edit Lead') }}</a>
                @endcan
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <section class="lead-360__card">
                    <h2 class="lead-360__card-title">{{ __('Lead Snapshot') }}</h2>
                    <div class="lead-360__grid">
                        <div><span class="lead-360__label">{{ __('Phone') }}</span><p>{{ $lead->phone ?: '—' }}</p></div>
                        <div><span class="lead-360__label">{{ __('Email') }}</span><p>{{ $lead->email ?: '—' }}</p></div>
                        <div><span class="lead-360__label">{{ __('Source') }}</span><p>{{ $lead->leadSource?->name ?? '—' }}</p></div>
                        <div><span class="lead-360__label">{{ __('Assigned To') }}</span><p>{{ $lead->assignee?->name ?? __('Unassigned') }}</p></div>
                        <div><span class="lead-360__label">{{ __('Probability') }}</span><p>{{ $lead->probability !== null ? $lead->probability.'%' : '—' }}</p></div>
                        <div><span class="lead-360__label">{{ __('Expected Close') }}</span><p>{{ $lead->expected_close_date?->format('d M Y') ?? '—' }}</p></div>
                    </div>
                    @if ($lead->notes)
                        <div class="lead-360__notes">
                            <span class="lead-360__label">{{ __('Notes') }}</span>
                            <p class="whitespace-pre-wrap">{{ $lead->notes }}</p>
                        </div>
                    @endif
                </section>

                <section class="lead-360__card">
                    <div class="lead-360__card-head">
                        <h2 class="lead-360__card-title">{{ __('Quotations') }}</h2>
                        @can('viewAny', App\Models\Sales\Quotation::class)
                            <a href="{{ route('admin.quotations.index', ['lead_id' => $lead->id]) }}" class="text-xs font-semibold text-indigo-700 hover:underline" data-turbo-frame="erp-main">
                                {{ __('View all') }}
                            </a>
                        @endcan
                    </div>

                    @if ($lead->quotations->isNotEmpty())
                        <ul class="lead-360__quote-list" role="list">
                            @foreach ($lead->quotations as $quotation)
                                <li>
                                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="lead-360__quote-row" data-turbo-frame="erp-main">
                                        <span class="font-mono text-sm font-semibold text-erp-primary">{{ $quotation->quotation_number }}</span>
                                        <span class="text-xs text-slate-500">{{ $quotation->quotation_date?->format('d M Y') }}</span>
                                        <x-admin.status-badge variant="neutral">{{ $quotation->status->value }}</x-admin.status-badge>
                                        <span class="text-sm font-medium tabular-nums">{{ number_format((float) $quotation->total_amount, 2) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500">{{ __('No quotations linked to this lead yet.') }}</p>
                        @can('quote', $lead)
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('admin.crm.leads.quotation.create', $lead) }}" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" data-turbo-frame="erp-main">{{ __('Create Quotation') }}</a>
                                <form method="POST" action="{{ route('admin.crm.leads.quotation.quick', $lead) }}">@csrf
                                    <button type="submit" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm">{{ __('Quick Quote') }}</button>
                                </form>
                            </div>
                        @endcan
                    @endif
                </section>

                <section class="lead-360__card">
                    <h2 class="lead-360__card-title">{{ __('Follow-ups') }}</h2>
                    @foreach ($lead->followUps as $fu)
                        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0">
                            <span>{{ $fu->scheduled_at->format('Y-m-d H:i') }} — {{ $fu->status->value }}</span>
                            @can('update', $lead)
                                <form method="POST" action="{{ route('admin.crm.leads.follow-ups.update', [$lead, $fu]) }}">@csrf @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button class="text-xs font-semibold text-emerald-700">{{ __('Complete') }}</button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                    @can('update', $lead)
                        <form method="POST" action="{{ route('admin.crm.leads.follow-ups.store', $lead) }}" class="mt-4 space-y-2">@csrf
                            <x-text-input name="scheduled_at" type="datetime-local" class="w-full" required />
                            <textarea name="notes" class="erp-input w-full text-sm" rows="2"></textarea>
                            <x-primary-button class="text-xs">{{ __('Schedule follow-up') }}</x-primary-button>
                        </form>
                    @endcan
                </section>
            </div>

            <aside class="space-y-4 xl:col-span-4 xl:sticky xl:top-20">
                <section class="lead-360__card">
                    <h2 class="lead-360__card-title">{{ __('Conversion') }}</h2>
                    <dl class="lead-360__rail">
                        <div>
                            <dt>{{ __('Customer') }}</dt>
                            <dd>{{ $lead->customer ? $lead->customer->company_name : __('Not linked') }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Quotations') }}</dt>
                            <dd>{{ $lead->quotations->count() }}</dd>
                        </div>
                    </dl>
                </section>

                @can('update', $lead)
                    @if ($lead->status !== App\Enums\LeadStatus::Lost)
                        <form method="POST" action="{{ route('admin.crm.leads.mark-lost', $lead) }}">@csrf
                            <button type="submit" class="crm-360__btn crm-360__btn--outline w-full justify-center">{{ __('Mark Lost') }}</button>
                        </form>
                    @endif
                @endcan

                @can('delete', $lead)
                    <form method="POST" action="{{ route('admin.crm.leads.destroy', $lead) }}" onsubmit="return confirm(@js(__('Delete this lead?')))">@csrf @method('DELETE')
                        <button type="submit" class="crm-360__btn crm-360__btn--danger w-full justify-center">{{ __('Delete Lead') }}</button>
                    </form>
                @endcan
            </aside>
        </div>
    </div>
</x-admin-layout>
