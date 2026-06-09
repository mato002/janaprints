<header class="crm-360__header">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <x-admin.crm-btn
                variant="ghost"
                size="sm"
                :href="route('admin.crm.leads.index')"
                class="!px-2.5"
                data-turbo-frame="erp-main"
            >← {{ __('Leads') }}</x-admin.crm-btn>
            <h1 class="crm-360__title">{{ $lead->lead_name }}</h1>
            <p class="crm-360__subtitle">
                @if ($lead->company_name)
                    <span>{{ $lead->company_name }}</span>
                @endif
                @if ($lead->branch)
                    <span class="text-slate-300" aria-hidden="true"> • </span>
                    <span>{{ $lead->branch->name }}</span>
                @endif
            </p>
            <p class="crm-360__since">
                {{ __('Lead since') }} {{ $lead->created_at?->format('M Y') ?? '—' }}
            </p>
            <span class="crm-360__status crm-360__status--{{ $lead->status->value }}">
                {{ strtoupper(str_replace('_', ' ', $lead->status->value)) }}
            </span>
        </div>

        <div class="crm-360__action-bar" x-data="{ moreOpen: false }">
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.crm.leads.360.partials.quotation-actions', [
                    'variant' => 'primary',
                    'quickVariant' => 'outline',
                    'size' => 'md',
                ])
            </div>

            @if ($lead->customer_id && $lead->customer)
                <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">
                    {{ __('Open customer') }}
                </a>
            @elseif(auth()->user()?->can('convert', $lead))
                <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" class="inline">@csrf
                    <button type="submit" class="crm-360__btn crm-360__btn--outline">{{ __('Convert lead') }}</button>
                </form>
            @endif

            <div class="relative">
                <button
                    type="button"
                    class="crm-360__btn crm-360__btn--ghost"
                    @click="moreOpen = !moreOpen"
                    :aria-expanded="moreOpen"
                    aria-haspopup="true"
                >
                    {{ __('More') }}
                    <svg class="h-4 w-4 transition-transform" :class="moreOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false" class="crm-360__more-menu" role="menu">
                    @can('create', App\Models\Crm\CustomerActivity::class)
                        <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('activities'); moreOpen = false">{{ __('Log activity') }}</button>
                    @endcan
                    @can('update', $lead)
                        <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('follow-ups'); moreOpen = false">{{ __('Schedule follow-up') }}</button>
                    @endcan
                    @can('update', $lead)
                        <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="crm-360__more-item" role="menuitem" data-turbo-frame="erp-main" @click="moreOpen = false">{{ __('Edit lead') }}</a>
                    @endcan
                    <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('quotations'); moreOpen = false">{{ __('Quotation list') }}</button>
                    @can('update', $lead)
                        @if ($lead->status !== App\Enums\LeadStatus::Lost)
                            <form method="POST" action="{{ route('admin.crm.leads.mark-lost', $lead) }}" class="crm-360__more-item p-0">@csrf
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm" role="menuitem">{{ __('Mark lost') }}</button>
                            </form>
                        @endif
                    @endcan
                    <hr class="crm-360__more-divider">
                    <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('timeline'); moreOpen = false">{{ __('View timeline') }}</button>
                </div>
            </div>
        </div>
    </div>
</header>
