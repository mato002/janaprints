<x-admin.modal-form :title="$panel['header']['job_number'] ?? $jobCard->job_card_number" maxWidth="4xl">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge">{{ $panel['header']['status'] ?? $jobCard->status->label() }}</span>
            <span class="text-sm text-slate-600">{{ $panel['header']['stage'] ?? '' }}</span>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500">{{ __('Customer') }}</dt><dd class="font-medium">{{ $panel['header']['customer'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Product') }}</dt><dd class="font-medium">{{ $panel['header']['product'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Required') }}</dt><dd>{{ $panel['header']['required_date'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Fulfilment') }}</dt><dd>{{ $panel['fulfilment']['status_label'] ?? '—' }}</dd></div>
        </dl>

        @if (! empty($panel['blockers']))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                <p class="mb-1 font-medium">{{ __('Blockers') }}</p>
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($panel['blockers'] as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-xs text-slate-500">{{ __('Close this preview and use the job panel on the floor for next-step actions.') }}</p>
    </div>
</x-admin.modal-form>
