@if ($active->customer)
    <a href="{{ route('admin.crm.customers.show', $active->customer) }}" class="erp-btn erp-btn--primary erp-btn--sm w-full" data-turbo-frame="erp-main">
        {{ __('Customer profile (360)') }}
    </a>
    <p class="mt-2 text-[11px] text-slate-500">{{ __('Phone, email, credit, and full history live on the customer profile — not duplicated here.') }}</p>
@else
    <p class="text-sm text-slate-500">{{ __('Link a customer to open their profile and ERP records.') }}</p>
@endif

@if ($context && ! empty($context['summary_compact']))
    @php $s = $context['summary_compact']; @endphp
    @if ((float) str_replace(',', '', $s['outstanding_balance']) > 0 || (int) $s['open_items_count'] > 0)
        <dl class="mt-4 grid grid-cols-2 gap-2 rounded-lg border border-erp-border bg-white p-3 text-xs">
            <div>
                <dt class="text-slate-500">{{ __('Outstanding') }}</dt>
                <dd class="font-semibold text-erp-primary">{{ $s['outstanding_balance'] }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Open ERP items') }}</dt>
                <dd class="font-semibold">{{ $s['open_items_count'] }}</dd>
            </div>
        </dl>
    @endif
@endif
