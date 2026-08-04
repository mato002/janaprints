@if (! empty($summary))
    @php $compact = (bool) ($compact ?? false); @endphp
    <div @class([
        'production-queue-summary',
        'production-queue-summary--compact' => $compact,
        'mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-lg border border-erp-border bg-white px-4 py-3 text-sm text-slate-600' => ! $compact,
    ])>
        <span>{{ __('Visible jobs') }}: {{ $summary['total_visible'] ?? 0 }}</span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span>{{ __('Waiting') }}: {{ $summary['waiting'] ?? 0 }}</span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span>{{ __('Running') }}: {{ $summary['running'] ?? 0 }}</span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span @class(['font-medium text-red-700' => (int) ($summary['overdue'] ?? 0) > 0])>
            {{ __('Overdue') }}: {{ $summary['overdue'] ?? 0 }}
        </span>
    </div>
@endif
