@php
    $summary = $controlCenter['summary'] ?? [];
    $compliance = $controlCenter['compliance'] ?? [];
    $percent = (int) ($summary['compliance_percent'] ?? $compliance['compliance_percent'] ?? 0);
    $governed = (int) ($summary['governed_forms'] ?? $compliance['governed_forms'] ?? 0);
    $total = (int) ($summary['operational_forms'] ?? $compliance['total_forms'] ?? 0);
    $gaps = $compliance['gaps'] ?? [];
    $healthy = $percent >= 100;
@endphp

<div class="mb-3 rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Governance Compliance') }}</p>
            <p class="mt-1 text-sm text-erp-primary">
                <strong class="{{ $healthy ? 'text-emerald-700' : 'text-amber-700' }}">{{ $percent }}%</strong>
                <span class="text-slate-500">
                    — {{ __(':governed of :total registry forms are wired to Form Controls', ['governed' => $governed, 'total' => $total]) }}
                </span>
            </p>
        </div>

        @if (! empty($gaps))
            <p class="text-xs text-slate-500">
                {{ __('Pending wiring') }}:
                <span class="font-medium text-erp-primary">{{ implode(', ', $gaps) }}</span>
            </p>
        @endif
    </div>
</div>
