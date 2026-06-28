@php $outsource = $tabData['outsource'] ?? []; @endphp

<x-admin.card class="mt-6" id="outsource">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Outsourced Production') }}</h3>

    @if ($outsource['vendor'] ?? null)
        <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2 mb-4">
            <div><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd class="font-medium">{{ $outsource['vendor']->vendor_name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Issue date') }}</dt><dd>{{ $outsource['issue_date']?->format('Y-m-d') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Expected return') }}</dt><dd>{{ $outsource['expected_return']?->format('Y-m-d') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Quoted cost') }}</dt><dd>{{ $outsource['quoted_cost'] !== null ? number_format($outsource['quoted_cost'], 2) : '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Actual cost') }}</dt><dd>{{ $outsource['actual_cost'] !== null ? number_format($outsource['actual_cost'], 2) : '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Margin exposure') }}</dt><dd>{{ isset($outsource['cost_exposure']['margin_impact']) && $outsource['cost_exposure']['margin_impact'] !== null ? number_format($outsource['cost_exposure']['margin_impact'], 2) : '—' }}</dd></div>
        </dl>
        @if ($outsource['notes'])
            <p class="text-sm text-slate-600 mb-4">{{ $outsource['notes'] }}</p>
        @endif
    @endif

    @if ($outsource['can_outsource'] ?? false)
        <form method="POST" action="{{ route('admin.production.job-cards.outsource', $jobCard) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 max-w-2xl">
            @csrf
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Production vendor') }}</label>
                <select name="outsource_vendor_id" class="erp-input w-full" required>
                    @foreach ($outsource['production_vendors'] ?? [] as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="erp-label">{{ __('Issue date') }}</label><input type="date" name="outsource_issue_date" class="erp-input w-full" required value="{{ now()->format('Y-m-d') }}"></div>
            <div><label class="erp-label">{{ __('Expected return') }}</label><input type="date" name="outsource_expected_return" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Quoted cost') }}</label><input type="number" step="0.01" name="outsource_quoted_cost" class="erp-input w-full"></div>
            <div class="md:col-span-2"><label class="erp-label">{{ __('Notes') }}</label><textarea name="outsource_notes" class="erp-input w-full" rows="2"></textarea></div>
            <div><button type="submit" class="erp-btn-primary">{{ __('Outsource production') }}</button></div>
        </form>
    @elseif ($outsource['can_return'] ?? false)
        <form method="POST" action="{{ route('admin.production.job-cards.outsource.return', $jobCard) }}" class="flex flex-wrap items-end gap-3 max-w-md">
            @csrf
            <div class="flex-1"><label class="erp-label">{{ __('Actual cost') }}</label><input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full"></div>
            <button type="submit" class="erp-btn-primary">{{ __('Mark returned') }}</button>
        </form>
    @elseif (! ($outsource['vendor'] ?? null))
        <p class="text-sm text-slate-500">{{ __('This job has not been outsourced.') }}</p>
    @endif
</x-admin.card>
