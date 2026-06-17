<div class="crm-360__grid crm-360__grid--overview">
    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Customer Information') }}</h2>
        <dl class="crm-360__dl">
            <div><dt>{{ __('Type') }}</dt><dd>{{ ucfirst($customer->customer_type->value) }}</dd></div>
            <div><dt>{{ __('Contact person') }}</dt><dd>{{ $customer->contact_person ?: '—' }}</dd></div>
            <div><dt>{{ __('Phone') }}</dt><dd>{{ $customer->phone ?: '—' }}</dd></div>
            <div><dt>{{ __('Email') }}</dt><dd>{{ $customer->email ?: '—' }}</dd></div>
            <div><dt>{{ __('City') }}</dt><dd>{{ $customer->city ?: '—' }}</dd></div>
            <div><dt>{{ __('Credit limit') }}</dt><dd>{{ $customer->credit_limit ? number_format((float) $customer->credit_limit, 2) : '—' }}</dd></div>
        </dl>
        @if ($customer->portalUser)
            <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50/60 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-800">{{ __('Client portal') }}</p>
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
                    <form method="POST" action="{{ route('admin.crm.customers.portal-invite', $customer) }}" class="mt-3" data-turbo-frame="_top">
                        @csrf
                        <button type="submit" class="crm-360__btn crm-360__btn--outline text-xs">
                            {{ __('Resend portal password link') }}
                        </button>
                    </form>
                @endcan
            </div>
        @elseif (filled($customer->email))
            <div class="mt-4 rounded-lg border border-amber-100 bg-amber-50/70 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-900">{{ __('Client portal') }}</p>
                <p class="mt-2 text-sm text-amber-950">
                    {{ __('This customer record has no portal login yet. Customers sign in at :url — invite them to create a password.', [
                        'url' => route('client.login'),
                    ]) }}
                </p>
                @can('inviteToPortal', $customer)
                    <form method="POST" action="{{ route('admin.crm.customers.portal-invite', $customer) }}" class="mt-3" data-turbo-frame="_top">
                        @csrf
                        <button type="submit" class="crm-360__btn crm-360__btn--primary text-xs">
                            {{ __('Send client portal invite') }}
                        </button>
                    </form>
                @endcan
            </div>
        @else
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">{{ __('Client portal') }}</p>
                <p class="mt-2 text-sm text-slate-600">
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
        @can('update', $customer)
            <div class="mt-3">
                <x-admin.crm-btn
                    variant="outline"
                    size="sm"
                    :href="route('admin.crm.customers.edit', $customer)"
                    data-turbo-frame="erp-main"
                >{{ __('View full profile') }}</x-admin.crm-btn>
            </div>
        @endcan
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Contact Summary') }}</h2>
            @can('update', $customer)
                <x-admin.crm-btn
                    variant="ghost"
                    size="sm"
                    :href="route('admin.crm.customers.edit', $customer)"
                    data-turbo-frame="erp-main"
                >{{ __('Manage contacts') }}</x-admin.crm-btn>
            @endcan
        </div>
        <ul class="crm-360__mini-list" role="list">
            @forelse ($customer->contacts as $contact)
                <li>
                    <span class="font-medium text-erp-primary">{{ $contact->name }}</span>
                    @if ($contact->is_primary)<span class="crm-360__pill">{{ __('Primary') }}</span>@endif
                    <span class="block text-[11px] text-slate-500">{{ $contact->phone ?: $contact->email ?: '—' }}</span>
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No contacts on file') }}</li>
            @endforelse
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Recent Activity') }}</h2>
            <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('activities')">{{ __('View all') }}</x-admin.crm-btn>
        </div>
        <ul class="crm-360__mini-list" role="list">
            @forelse ($customer->activities->sortByDesc('activity_at')->take(4) as $activity)
                <li>
                    @can('view', $activity)
                        <a href="{{ route('admin.commercial.activities.show', $activity) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $activity->subject }}</a>
                    @else
                        <span class="font-medium">{{ $activity->subject }}</span>
                    @endcan
                    <span class="block text-[11px] text-slate-500">{{ $activity->activity_at?->diffForHumans() }}</span>
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No activities logged') }}</li>
            @endforelse
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Recent Conversations') }}</h2>
            <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('conversations')">{{ __('View all') }}</x-admin.crm-btn>
        </div>
        <ul class="crm-360__mini-list" role="list">
            @forelse ($inboxConversations->take(4) as $conv)
                <li>
                    <a href="{{ route('admin.communications.inbox.index', ['conversation' => $conv->id]) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $conv->conversation_code }}</a>
                    <span class="block text-[11px] text-slate-500">{{ $conv->status->label() }} · {{ $conv->last_activity_at?->diffForHumans() }}</span>
                </li>
            @empty
                @forelse ($whatsappConversations->take(4) as $conv)
                    <li>
                        <a href="{{ route('admin.communications.whatsapp.conversations.show', $conv) }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $conv->conversation_code }}</a>
                        <span class="block text-[11px] text-slate-500">{{ __('WhatsApp') }} · {{ $conv->updated_at?->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="crm-360__empty-inline">{{ __('No conversations yet') }}</li>
                @endforelse
            @endforelse
        </ul>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Open Jobs') }}</h2>
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
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Outstanding Invoices') }}</h2>
            <x-admin.crm-btn type="button" variant="ghost" size="sm" @click="setTab('commercial')">{{ __('View all') }}</x-admin.crm-btn>
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
</div>
