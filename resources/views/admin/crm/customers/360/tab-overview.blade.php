@php
    $ordersCount = (int) ($commercial['counts']['orders'] ?? 0);
    $quotesCount = (int) ($commercial['counts']['quotations'] ?? 0);
    $openJobsCount = $openJobs->count();
    $outstanding = null;
    $complaintCount = null;
    $lastInteraction = null;
    $hasComplaintsKpi = false;
    foreach ($kpis as $kpiRow) {
        $key = $kpiRow['key'] ?? null;
        if ($key === 'balance') {
            $outstanding = $kpiRow['value'];
        } elseif ($key === 'complaints') {
            $hasComplaintsKpi = true;
            $complaintCount = (int) ($kpiRow['value'] ?? 0);
        } elseif ($key === 'activity' && ! empty($kpiRow['value'])) {
            $lastInteraction = $kpiRow['value'];
        }
    }

    $nextAction = __('Review customer profile and confirm contact details');
    $nextActionTab = 'overview';
    $nextActionKind = 'neutral';

    if (($outstanding ?? 0) > 0) {
        $nextAction = __('Follow up on outstanding balance of :amount', [
            'amount' => number_format((float) $outstanding, 2),
        ]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'attention';
    } elseif (($complaintCount ?? 0) > 0) {
        $nextAction = __('Address :count open complaint(s)', ['count' => $complaintCount]);
        $nextActionTab = 'overview';
        $nextActionKind = 'attention';
    } elseif ($openJobsCount > 0) {
        $nextAction = __('Monitor :count open production job(s)', ['count' => $openJobsCount]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif ($ordersCount > 0) {
        $nextAction = __('Follow up on :count sales order(s)', ['count' => $ordersCount]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif ($quotesCount > 0) {
        $nextAction = __('Advance open quotations');
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif (auth()->user()->can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)) {
        $nextAction = __('Start a conversation to re-engage this customer');
        $nextActionTab = 'conversations';
        $nextActionKind = 'info';
    }

    $openWorkParts = collect([
        $ordersCount > 0 ? __(':count sales orders', ['count' => $ordersCount]) : null,
        $quotesCount > 0 ? __(':count quotes', ['count' => $quotesCount]) : null,
        $openJobsCount > 0 ? __(':count production jobs', ['count' => $openJobsCount]) : null,
    ])->filter();
@endphp

<div class="crm-360__overview">
    <div class="crm-360__overview-main">
        <section class="crm-360__panel-block crm-360__panel-block--primary">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title">{{ __('Customer profile') }}</h2>
                @can('update', $customer)
                    <x-admin.crm-btn
                        variant="ghost"
                        size="sm"
                        :href="route('admin.crm.customers.edit', $customer)"
                        data-turbo-frame="erp-main"
                    >{{ __('Edit') }}</x-admin.crm-btn>
                @endcan
            </div>
            <dl class="crm-360__dl">
                <div><dt>{{ __('Type') }}</dt><dd>{{ ucfirst($customer->customer_type->value) }}</dd></div>
                <div><dt>{{ __('Contact person') }}</dt><dd>{{ $customer->contact_person ?: '—' }}</dd></div>
                <div><dt>{{ __('Phone') }}</dt><dd>{{ $customer->phone ?: '—' }}</dd></div>
                <div><dt>{{ __('Email') }}</dt><dd class="crm-360__truncate">{{ $customer->email ?: '—' }}</dd></div>
                <div><dt>{{ __('City') }}</dt><dd>{{ $customer->city ?: '—' }}</dd></div>
                <div><dt>{{ __('Credit limit') }}</dt><dd>{{ $customer->credit_limit ? number_format((float) $customer->credit_limit, 2) : '—' }}</dd></div>
            </dl>

            @if ($customer->portalUser)
                <div class="crm-360__inset crm-360__inset--info">
                    <p class="crm-360__inset-label">{{ __('Client portal') }}</p>
                    <dl class="crm-360__dl mt-2">
                        <div><dt>{{ __('Portal user') }}</dt><dd>{{ $customer->portalUser->name }}</dd></div>
                        <div><dt>{{ __('Login email') }}</dt><dd>{{ $customer->portalUser->email }}</dd></div>
                        <div>
                            <dt>{{ __('Portal status') }}</dt>
                            <dd>{{ $customer->portalUser->is_active ? __('Active') : __('Inactive') }}</dd>
                        </div>
                        @php
                            $lastPortalLogin = $customer->portalUser->sessions->sortByDesc('login_at')->first();
                        @endphp
                        <div>
                            <dt>{{ __('Last portal login') }}</dt>
                            <dd>{{ $lastPortalLogin?->login_at?->diffForHumans() ?? '—' }}</dd>
                        </div>
                    </dl>
                    @can('inviteToPortal', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.portal-invite', $customer) }}" class="mt-3" data-turbo-frame="erp-main">
                            @csrf
                            <button type="submit" class="crm-360__btn crm-360__btn--outline text-xs">
                                {{ __('Resend portal password link') }}
                            </button>
                        </form>
                    @endcan
                </div>
            @elseif (filled($customer->email))
                <div class="crm-360__inset crm-360__inset--warn">
                    <p class="crm-360__inset-label">{{ __('Client portal') }}</p>
                    <p class="mt-1.5 text-sm text-amber-950">
                        {{ __('This customer record has no portal login yet. Customers sign in at :url — invite them to create a password.', [
                            'url' => route('client.login'),
                        ]) }}
                    </p>
                    @can('inviteToPortal', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.portal-invite', $customer) }}" class="mt-3" data-turbo-frame="erp-main">
                            @csrf
                            <button type="submit" class="crm-360__btn crm-360__btn--primary text-xs">
                                {{ __('Send client portal invite') }}
                            </button>
                        </form>
                    @endcan
                </div>
            @else
                <div class="crm-360__inset">
                    <p class="crm-360__inset-label">{{ __('Client portal') }}</p>
                    <p class="mt-1.5 text-sm text-slate-600">
                        {{ __('Add an email address to this customer profile before sending a portal invite.') }}
                    </p>
                </div>
            @endif

            @if ($customer->segments->isNotEmpty())
                <p class="mt-2 text-[11px] text-slate-500">
                    {{ __('Segments') }}:
                    {{ $customer->segments->pluck('name')->join(', ') }}
                </p>
            @endif
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title">{{ __('Contacts') }}</h2>
                @can('update', $customer)
                    <x-admin.crm-btn
                        variant="ghost"
                        size="sm"
                        :href="route('admin.crm.customers.edit', $customer)"
                        data-turbo-frame="erp-main"
                    >{{ __('Manage') }}</x-admin.crm-btn>
                @endcan
            </div>
            @if ($customer->contacts->isNotEmpty())
                <ul class="crm-360__mini-list" role="list">
                    @foreach ($customer->contacts as $contact)
                        <li>
                            <span class="font-medium text-erp-primary">{{ $contact->name }}</span>
                            @if ($contact->is_primary)<span class="crm-360__pill">{{ __('Primary') }}</span>@endif
                            <span class="block text-[11px] text-slate-500">{{ $contact->phone ?: $contact->email ?: '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="crm-360__empty">
                    <p class="crm-360__empty-title">{{ __('No contacts added') }}</p>
                    <p class="crm-360__empty-body">{{ __('Add the customer’s purchasing, finance or delivery contact.') }}</p>
                    @can('update', $customer)
                        <x-admin.crm-btn
                            variant="outline"
                            size="sm"
                            :href="route('admin.crm.customers.edit', $customer)"
                            class="mt-2"
                            data-turbo-frame="erp-main"
                        >{{ __('Add contact') }}</x-admin.crm-btn>
                    @endcan
                </div>
            @endif
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title">{{ __('Active commercial work') }}</h2>
                <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('commercial')">{{ __('View all') }}</x-admin.crm-btn>
            </div>
            <div class="crm-360__split-lists">
                <div>
                    <p class="crm-360__subhead">{{ __('Sales orders') }}</p>
                    <ul class="crm-360__mini-list" role="list">
                        @forelse ($commercial['orders']->take(4) as $row)
                            <li>
                                @if ($row['url'])
                                    <a href="{{ $row['url'] }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $row['number'] }}</a>
                                @else
                                    <span class="font-medium">{{ $row['number'] }}</span>
                                @endif
                                <span class="block text-[11px] text-slate-500">{{ $row['status'] }}</span>
                            </li>
                        @empty
                            <li class="crm-360__empty-inline">{{ __('No sales orders yet') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <p class="crm-360__subhead">{{ __('Open jobs') }}</p>
                    <ul class="crm-360__mini-list" role="list">
                        @forelse ($openJobs as $job)
                            <li>
                                @if (Route::has('admin.production.job-cards.show'))
                                    <a href="{{ route('admin.production.job-cards.show', $job) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $job->job_card_number }}</a>
                                @else
                                    <span class="font-medium">{{ $job->job_card_number }}</span>
                                @endif
                                <span class="block text-[11px] text-slate-500">{{ \App\Support\EnumLabel::of($job->status) }}</span>
                            </li>
                        @empty
                            <li class="crm-360__empty-inline">{{ __('No open production jobs') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title">{{ __('Recent activity') }}</h2>
                <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('activities')">{{ __('View all') }}</x-admin.crm-btn>
            </div>
            @php
                $recentActivities = $customer->activities->sortByDesc('activity_at')->take(4);
                $hasInbox = $inboxConversations->isNotEmpty();
                $hasWhatsapp = $whatsappConversations->isNotEmpty();
            @endphp
            @if ($recentActivities->isNotEmpty() || $hasInbox || $hasWhatsapp)
                <ul class="crm-360__mini-list" role="list">
                    @foreach ($recentActivities as $activity)
                        <li>
                            @can('view', $activity)
                                <a href="{{ route('admin.commercial.activities.show', $activity) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $activity->subject }}</a>
                            @else
                                <span class="font-medium">{{ $activity->subject }}</span>
                            @endcan
                            <span class="block text-[11px] text-slate-500">{{ $activity->activity_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                    @forelse ($inboxConversations->take(3) as $conv)
                        <li>
                            <a href="{{ route('admin.communications.inbox.index', ['conversation' => $conv->id]) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $conv->conversation_code }}</a>
                            <span class="block text-[11px] text-slate-500">{{ $conv->status->label() }} · {{ $conv->last_activity_at?->diffForHumans() }}</span>
                        </li>
                    @empty
                        @foreach ($whatsappConversations->take(3) as $conv)
                            <li>
                                <a href="{{ route('admin.communications.whatsapp.conversations.show', $conv) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $conv->conversation_code }}</a>
                                <span class="block text-[11px] text-slate-500">{{ __('WhatsApp') }} · {{ $conv->updated_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    @endforelse
                </ul>
            @else
                <div class="crm-360__empty">
                    <p class="crm-360__empty-title">{{ __('No recent activity') }}</p>
                    <p class="crm-360__empty-body">{{ __('Calls, notes, messages and order updates will appear here.') }}</p>
                    <div class="crm-360__empty-actions">
                        @can('update', $customer)
                            <x-admin.crm-btn type="button" variant="outline" size="sm" @click="setTab('notes')">{{ __('Add note') }}</x-admin.crm-btn>
                        @endcan
                        @can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
                            <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" data-turbo-frame="erp-main">
                                @csrf
                                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">{{ __('Start conversation') }}</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endif
        </section>
    </div>

    <aside class="crm-360__overview-aside">
        <section class="crm-360__panel-block crm-360__panel-block--health">
            <h2 class="crm-360__card-title">{{ __('Relationship summary') }}</h2>
            <dl class="crm-360__health-list">
                <div>
                    <dt>{{ __('Customer health') }}</dt>
                    <dd>
                        <span class="crm-360__status crm-360__status--{{ $customer->status->value }} crm-360__status--inline">
                            {{ ucfirst($customer->status->value) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>{{ __('Last interaction') }}</dt>
                    <dd>{{ $lastInteraction ? $lastInteraction->diffForHumans() : '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Open work') }}</dt>
                    <dd>{{ $openWorkParts->isNotEmpty() ? $openWorkParts->join(', ') : __('None') }}</dd>
                </div>
                <div>
                    <dt>{{ __('Outstanding') }}</dt>
                    <dd class="{{ ($outstanding ?? 0) > 0 ? 'crm-360__value--alert' : '' }}">
                        {{ $outstanding !== null ? number_format((float) $outstanding, 2) : '—' }}
                    </dd>
                </div>
                @if ($hasComplaintsKpi)
                    <div>
                        <dt>{{ __('Complaints') }}</dt>
                        <dd class="{{ ($complaintCount ?? 0) > 0 ? 'crm-360__value--alert' : '' }}">
                            {{ ($complaintCount ?? 0) > 0 ? __(':count open', ['count' => $complaintCount]) : __('None open') }}
                        </dd>
                    </div>
                @endif
                @if ($canJobs)
                    <div>
                        <dt>{{ __('Production jobs') }}</dt>
                        <dd>{{ $openJobsCount > 0 ? __(':count open', ['count' => $openJobsCount]) : __('None open') }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="crm-360__panel-block crm-360__panel-block--next crm-360__panel-block--{{ $nextActionKind }}">
            <h2 class="crm-360__card-title">{{ __('Next action') }}</h2>
            <p class="crm-360__next-copy">{{ $nextAction }}</p>
            <x-admin.crm-btn type="button" variant="primary" size="sm" class="mt-3" @click="setTab(@js($nextActionTab))">
                {{ __('Go') }}
            </x-admin.crm-btn>
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title">{{ __('Financial snapshot') }}</h2>
                <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('commercial')">{{ __('Details') }}</x-admin.crm-btn>
            </div>
            <ul class="crm-360__mini-list" role="list">
                @forelse ($openInvoices as $invoice)
                    <li>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $invoice->invoice_number }}</a>
                        <span class="block text-[11px] text-slate-500">{{ number_format((float) $invoice->balance_due, 2) }} {{ __('due') }}</span>
                    </li>
                @empty
                    <li class="crm-360__empty-inline">{{ __('No outstanding invoices') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="crm-360__panel-block crm-360__panel-block--meta">
            <h2 class="crm-360__card-title">{{ __('Account') }}</h2>
            <dl class="crm-360__health-list">
                <div>
                    <dt>{{ __('Branch') }}</dt>
                    <dd>{{ $customer->branch?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Status') }}</dt>
                    <dd>{{ ucfirst($customer->status->value) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Customer since') }}</dt>
                    <dd>{{ $customer->created_at?->format('M Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Code') }}</dt>
                    <dd class="font-mono">{{ $customer->customer_code }}</dd>
                </div>
            </dl>
        </section>
    </aside>
</div>
