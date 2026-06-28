@if (! empty($summary))
    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-lg border border-erp-border bg-white px-4 py-3 text-sm text-slate-600">
        <span><span class="font-medium text-slate-800">{{ __('Visible jobs') }}:</span> {{ $summary['total_visible'] ?? 0 }}</span>
        <span><span class="font-medium text-slate-800">{{ __('Waiting') }}:</span> {{ $summary['waiting'] ?? 0 }}</span>
        <span><span class="font-medium text-slate-800">{{ __('Running') }}:</span> {{ $summary['running'] ?? 0 }}</span>
        <span><span class="font-medium text-slate-800">{{ __('Overdue') }}:</span> {{ $summary['overdue'] ?? 0 }}</span>
        <span><span class="font-medium text-slate-800">{{ __('Completed today') }}:</span> {{ $summary['completed_today'] ?? 0 }}</span>
    </div>
@endif
