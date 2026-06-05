<section class="crm-360__card">
    <h2 class="crm-360__card-title">{{ __('Request Summary') }}</h2>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Service') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->service_needed }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Category') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->service_needed }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Quantity') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->quantity ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Deadline') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->deadline ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Requested Date') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $quoteRequest->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Source') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ ucfirst($quoteRequest->source) }}</p>
        </div>
    </div>

    <div class="mt-5 rounded-xl border border-amber-100 bg-amber-50/60 p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">{{ __('Customer Notes') }}</p>
        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-800">{{ $quoteRequest->message }}</p>
    </div>
</section>
