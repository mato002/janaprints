<x-admin.card class="mb-6">
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Customer') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-900">
                @if ($quotation->customer)
                    <a href="{{ route('admin.crm.customers.show', $quotation->customer) }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main">
                        {{ $quotation->customer->company_name }}
                    </a>
                @elseif ($quotation->lead)
                    <span>{{ $quotation->lead->company_name ?? $quotation->lead->lead_name }}</span>
                    <span class="text-xs text-slate-400">({{ __('Lead') }})</span>
                @else
                    <span class="text-slate-400">{{ __('No customer') }}</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Quote date') }}</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Valid until') }}</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Sales rep') }}</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $quotation->preparer?->name ?? '—' }}</dd>
        </div>
    </dl>
</x-admin.card>
