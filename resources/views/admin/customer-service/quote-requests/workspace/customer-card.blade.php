<section class="crm-360__card mb-4">
    <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-start">
        <div class="min-w-0 space-y-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $quoteRequest->name }}</h2>
                @if ($quoteRequest->company)
                    <p class="mt-1 text-base font-medium text-slate-600">{{ $quoteRequest->company }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-4 text-sm">
                <a href="mailto:{{ $quoteRequest->email }}" class="font-medium text-erp-primary hover:text-erp-accent">{{ $quoteRequest->email }}</a>
                <a href="tel:{{ preg_replace('/\s+/', '', $quoteRequest->phone) }}" class="font-medium text-erp-primary hover:text-erp-accent">{{ $quoteRequest->phone }}</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Service') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->service_needed }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Quantity') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->quantity ?: '—' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Submitted') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->created_at->format('d M Y') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Deadline') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->deadline ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="flex min-w-[14rem] flex-col gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Quick Actions') }}</p>
            @foreach ($workspace['quick_actions'] as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="crm-360__btn {{ ($action['variant'] ?? '') === 'primary' ? 'crm-360__btn--primary' : 'crm-360__btn--outline' }} w-full justify-center"
                    @if (! empty($action['external'])) target="_blank" rel="noopener" @else data-turbo-frame="erp-main" @endif
                >{{ $action['label'] }}</a>
            @endforeach
            @can('update', $quoteRequest)
                <form method="POST" action="{{ route('admin.public-quote-requests.update-status', $quoteRequest) }}" class="mt-1">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="spam">
                    <button type="submit" class="crm-360__btn crm-360__btn--danger w-full justify-center" onclick="return confirm(@js(__('Reject this quote request?')))">
                        {{ __('Reject Request') }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</section>
