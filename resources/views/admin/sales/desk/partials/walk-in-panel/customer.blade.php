@if (! empty($panel['empty']))
    <p class="text-sm text-slate-600">{{ $panel['empty'] }}</p>
@else
    <div class="mb-3">
        <p class="truncate text-sm font-semibold text-slate-900">{{ $panel['name'] }}</p>
        @if (! empty($panel['customer_type']))
            <p class="text-xs text-slate-500">{{ $panel['customer_type'] }}</p>
        @endif
    </div>

    @if (count($panel['warnings'] ?? []) > 0)
        <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
            @foreach ($panel['warnings'] as $warning)
                <li @class([
                    'flex items-start gap-1.5',
                    'text-rose-800' => ($warning['severity'] ?? '') === 'danger',
                    'text-amber-900' => ($warning['severity'] ?? '') !== 'danger',
                ])>
                    <span aria-hidden="true">⚠</span>
                    <span>{{ $warning['message'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
        @if ($panel['outstanding_balance'] ?? null)
            <div>
                <dt class="text-xs text-slate-500">{{ __('Outstanding') }}</dt>
                <dd class="font-mono text-amber-800">{{ $panel['outstanding_balance'] }}</dd>
            </div>
        @endif
        @if ($panel['credit_limit'] ?? null)
            <div>
                <dt class="text-xs text-slate-500">{{ __('Credit limit') }}</dt>
                <dd class="font-mono text-slate-900">{{ $panel['credit_limit'] }}</dd>
            </div>
        @endif
        @if ($panel['overdue_amount'] ?? null)
            <div>
                <dt class="text-xs text-slate-500">{{ __('Overdue') }}</dt>
                <dd class="font-mono text-rose-700">{{ $panel['overdue_amount'] }}</dd>
            </div>
        @endif
        @if (($panel['artwork_pending_count'] ?? 0) > 0)
            <div>
                <dt class="text-xs text-slate-500">{{ __('Artwork waiting') }}</dt>
                <dd class="font-medium text-violet-800">{{ $panel['artwork_pending_count'] }}</dd>
            </div>
        @endif
        <div class="col-span-2">
            <dt class="text-xs text-slate-500">{{ __('Contact') }}</dt>
            <dd class="text-slate-800">
                @if ($panel['contact_person'] ?? null)
                    <span class="block">{{ $panel['contact_person'] }}</span>
                @endif
                {{ $panel['phone'] ?? '—' }} · {{ $panel['email'] ?? '—' }}
            </dd>
        </div>
        @if ($panel['last_order'] ?? null)
            <div class="col-span-2">
                <dt class="text-xs text-slate-500">{{ __('Last order') }}</dt>
                <dd class="text-slate-900">
                    {{ $panel['last_order']['product'] ?? $panel['last_order']['order_number'] }}
                    <span class="text-xs text-slate-500">· {{ $panel['last_order']['order_date'] ?? '' }}</span>
                </dd>
            </div>
        @endif
    </dl>

    @if (count($panel['open_quotations'] ?? []) > 0)
        <div class="mt-3 border-t border-erp-border pt-3">
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Open quotes') }}</p>
            <ul class="space-y-1 text-xs">
                @foreach ($panel['open_quotations'] as $quote)
                    <li>
                        <a href="{{ $quote['create_url'] }}" class="text-erp-primary hover:underline" data-erp-modal-open>{{ $quote['quotation_number'] }}</a>
                        <span class="text-slate-500"> · {{ $quote['status'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
