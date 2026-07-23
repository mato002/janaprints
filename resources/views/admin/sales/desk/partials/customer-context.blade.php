@if ($customerContext)
    <x-admin.card>
        <div class="mb-2 flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold text-slate-900">{{ $customerContext['name'] }}</h3>
                @if ($customerContext['customer_type'] ?? null)
                    <p class="text-xs text-slate-500">{{ $customerContext['customer_type'] }}</p>
                @endif
            </div>
            <div class="flex shrink-0 gap-2">
                @if ($deskUrls['customer_360'] ?? null)
                    <a href="{{ $deskUrls['customer_360'] }}" class="text-xs text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('360') }}</a>
                @endif
                @if ($deskUrls['edit_customer'] ?? null)
                    <a href="{{ $deskUrls['edit_customer'] }}" class="text-xs text-erp-primary hover:underline" data-erp-modal-open>{{ __('Edit') }}</a>
                @endif
            </div>
        </div>

        @if (count($customerContext['warnings'] ?? []) > 0)
            <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
                @foreach ($customerContext['warnings'] as $warning)
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
            @if ($customerContext['outstanding_balance'] ?? null)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Outstanding') }}</dt>
                    <dd class="font-mono text-amber-800">{{ $customerContext['outstanding_balance'] }}</dd>
                </div>
            @endif
            @if ($customerContext['credit_limit'] ?? null)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Credit limit') }}</dt>
                    <dd class="font-mono text-slate-900">{{ $customerContext['credit_limit'] }}</dd>
                </div>
            @endif
            @if ($customerContext['overdue_amount'] ?? null)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Overdue') }}</dt>
                    <dd class="font-mono text-rose-700">{{ $customerContext['overdue_amount'] }}</dd>
                </div>
            @endif
            @if (($customerContext['open_quotes_count'] ?? 0) > 0)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Active quotes') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $customerContext['open_quotes_count'] }}</dd>
                </div>
            @endif
            @if (($customerContext['open_jobs_count'] ?? 0) > 0)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Open jobs') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $customerContext['open_jobs_count'] }}</dd>
                </div>
            @endif
            @if (($customerContext['artwork_pending_count'] ?? 0) > 0)
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Artwork waiting') }}</dt>
                    <dd class="font-medium text-violet-800">{{ $customerContext['artwork_pending_count'] }}</dd>
                </div>
            @endif
            <div class="col-span-2">
                <dt class="text-xs text-slate-500">{{ __('Contact') }}</dt>
                <dd class="text-slate-800">{{ $customerContext['phone'] ?? '—' }} · {{ $customerContext['email'] ?? '—' }}</dd>
            </div>
            @if ($customerContext['last_order'] ?? null)
                <div class="col-span-2">
                    <dt class="text-xs text-slate-500">{{ __('Last order') }}</dt>
                    <dd class="text-slate-900">
                        {{ $customerContext['last_order']['product'] ?? $customerContext['last_order']['order_number'] }}
                        <span class="text-xs text-slate-500">· {{ $customerContext['last_order']['order_date'] ?? '' }}</span>
                    </dd>
                </div>
            @endif
        </dl>

        @if (count($customerContext['frequent_products'] ?? []) > 0)
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Often ordered') }}</p>
                <ul class="space-y-1 text-xs text-slate-700">
                    @foreach ($customerContext['frequent_products'] as $product)
                        <li>{{ $product['item_name'] }} <span class="text-slate-400">×{{ $product['order_count'] }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($customerContext['recent_orders'] ?? []) > 0)
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Recent orders') }}</p>
                <ul class="space-y-1 text-xs">
                    @foreach ($customerContext['recent_orders'] as $recent)
                        <li class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <a href="{{ $recent['desk_url'] }}" class="text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $recent['order_number'] }}</a>
                            <span class="text-slate-500">{{ $recent['total_amount'] }}</span>
                            @can('create', App\Models\Sales\SalesOrder::class)
                                <form
                                    method="POST"
                                    action="{{ $recent['repeat_url'] }}"
                                    class="inline"
                                    onsubmit="return confirm(@js(__('Create a repeat order from :number?', ['number' => $recent['order_number']])))"
                                >
                                    @csrf
                                    <button type="submit" class="text-[10px] font-semibold uppercase tracking-wide text-erp-accent hover:underline">{{ __('Quote again') }}</button>
                                </form>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($customerContext['open_quotations'] ?? []) > 0)
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Open quotes') }}</p>
                <ul class="space-y-1 text-xs">
                    @foreach ($customerContext['open_quotations'] as $quote)
                        <li>
                            <a href="{{ $quote['create_url'] }}" class="text-erp-primary hover:underline" data-erp-modal-open>{{ $quote['quotation_number'] }}</a>
                            <span class="text-slate-500"> · {{ $quote['status'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($customerContext['timeline'] ?? []) > 0)
            <div class="mt-3 border-t border-erp-border pt-3">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Recent activity') }}</p>
                <ul class="space-y-2 text-xs">
                    @foreach ($customerContext['timeline'] as $event)
                        <li>
                            @if ($event['url'] ?? null)
                                <a href="{{ $event['url'] }}" class="font-medium text-erp-primary hover:underline" @if (str_contains($event['url'], 'from=sales-desk')) data-erp-modal-open @else data-turbo-frame="erp-main" @endif>{{ $event['title'] }}</a>
                            @else
                                <p class="font-medium text-slate-900">{{ $event['title'] }}</p>
                            @endif
                            @if ($event['description'] ?? null)
                                <p class="text-slate-600">{{ $event['description'] }}</p>
                            @endif
                            <p class="text-slate-400">{{ $event['at'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-admin.card>
@endif
