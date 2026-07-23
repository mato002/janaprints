<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['export']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['export']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
    'bg-slate-100 text-slate-600' => $export->status->value === 'queued',
    'bg-blue-50 text-blue-700' => $export->status->value === 'processing',
    'bg-emerald-50 text-emerald-700' => $export->status->value === 'completed' && ! $export->isExpired(),
    'bg-rose-50 text-rose-700' => $export->status->value === 'failed',
    'bg-amber-50 text-amber-700' => $export->isExpired() || $export->status->value === 'expired',
]); ?>">
    <?php echo e(match (true) {
        $export->isExpired() || $export->status->value === 'expired' => __('Expired'),
        $export->status->value === 'queued' => __('Queued'),
        $export->status->value === 'processing' => __('Processing'),
        $export->status->value === 'completed' => __('Completed'),
        $export->status->value === 'failed' => __('Failed'),
        default => ucfirst($export->status->value),
    }); ?>

</span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\exports\partials\status-badge.blade.php ENDPATH**/ ?>