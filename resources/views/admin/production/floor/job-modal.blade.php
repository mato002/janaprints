<x-admin.modal-form :title="$panel['header']['job_number'] ?? $jobCard->job_card_number" maxWidth="4xl">
    <div class="space-y-4">
        <div class="production-floor-panel-hero">
            <dl>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">{{ __('Customer') }}</dt>
                    <dd class="text-base font-semibold text-erp-primary">{{ $panel['header']['customer'] ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">{{ __('Product') }}</dt>
                    <dd class="font-medium">{{ $panel['header']['product'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Artwork / job status') }}</dt>
                    <dd>{{ $panel['job']['status_label'] ?? ($panel['header']['status'] ?? $jobCard->status->label()) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Production stage') }}</dt>
                    <dd>{{ $panel['header']['stage'] ?? '' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Required date') }}</dt>
                    <dd>{{ $panel['header']['required_date'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Materials / fulfilment') }}</dt>
                    <dd>{{ $panel['fulfilment']['status_label'] ?? __('Not started') }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="production-floor-panel-status-card">
                <dt>{{ __('Machine') }}</dt>
                <dd>{{ $panel['job']['machine'] ?? '—' }}</dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt>{{ __('Work center') }}</dt>
                <dd>{{ $panel['job']['work_center'] ?? '—' }}</dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt>{{ __('Priority') }}</dt>
                <dd class="capitalize">{{ $panel['job']['priority_label'] ?? '—' }}</dd>
            </div>
            <div class="production-floor-panel-status-card">
                <dt>{{ __('Vendor') }}</dt>
                <dd>{{ $panel['job']['vendor'] ?? __('Not at vendor') }}</dd>
            </div>
        </div>

        @if (! empty($panel['blockers']))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                <p class="mb-1 font-medium">{{ __('Production blockers') }}</p>
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($panel['blockers'] as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($jobCard->production_notes_snapshot)
            <div class="rounded-lg border border-erp-border bg-white p-3">
                <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production notes') }}</h3>
                <p class="whitespace-pre-wrap text-sm text-slate-700">{{ $jobCard->production_notes_snapshot }}</p>
            </div>
        @endif

        <details class="rounded-lg border border-erp-border bg-slate-50/60 p-3">
            <summary class="cursor-pointer text-sm font-medium text-slate-700">{{ __('Commercial summary') }}</summary>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Job status') }}</dt><dd>{{ $panel['header']['status'] ?? $jobCard->status->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Fulfilment') }}</dt><dd>{{ $panel['fulfilment']['status_label'] ?? '—' }}</dd></div>
            </dl>
        </details>

        <p class="text-xs text-slate-500">{{ __('Close this preview and use the job panel on the floor for next-step actions.') }}</p>
    </div>
</x-admin.modal-form>
