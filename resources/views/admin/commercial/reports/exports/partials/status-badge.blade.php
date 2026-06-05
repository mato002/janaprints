@props(['export'])

<span @class([
    'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
    'bg-slate-100 text-slate-600' => $export->status->value === 'queued',
    'bg-blue-50 text-blue-700' => $export->status->value === 'processing',
    'bg-emerald-50 text-emerald-700' => $export->status->value === 'completed' && ! $export->isExpired(),
    'bg-rose-50 text-rose-700' => $export->status->value === 'failed',
    'bg-amber-50 text-amber-700' => $export->isExpired() || $export->status->value === 'expired',
])>
    {{ match (true) {
        $export->isExpired() || $export->status->value === 'expired' => __('Expired'),
        $export->status->value === 'queued' => __('Queued'),
        $export->status->value === 'processing' => __('Processing'),
        $export->status->value === 'completed' => __('Completed'),
        $export->status->value === 'failed' => __('Failed'),
        default => ucfirst($export->status->value),
    } }}
</span>
