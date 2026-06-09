@can('createQuotation', $lead)
    <form method="POST" action="{{ route('admin.crm.leads.create-quotation', $lead) }}" class="inline">@csrf
        <x-admin.crm-btn type="submit" :variant="$variant ?? 'primary'" :size="$size ?? 'sm'">{{ __('Create quotation') }}</x-admin.crm-btn>
    </form>
    <form method="POST" action="{{ route('admin.crm.leads.quick-quote', $lead) }}" class="inline">@csrf
        <x-admin.crm-btn type="submit" :variant="$quickVariant ?? 'outline'" :size="$size ?? 'sm'">{{ __('Quick quote') }}</x-admin.crm-btn>
    </form>
@elseif(auth()->user()?->can('quotations.create'))
    <p class="text-sm text-slate-500">
        @if (! $quotationActions['auto_convert_enabled'] && $quotationActions['needs_customer'])
            {{ __('Enable auto-convert in CRM settings or convert this lead to a customer first.') }}
        @else
            {{ __('Customer creation permission is required to quote from this lead.') }}
        @endif
    </p>
    @can('convert', $lead)
        <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" class="inline">@csrf
            <x-admin.crm-btn type="submit" variant="outline" :size="$size ?? 'sm'">{{ __('Convert lead') }}</x-admin.crm-btn>
        </form>
    @endcan
@endcan
