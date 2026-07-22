<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status' => 'unknown',
    'label' => null,
]));

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

foreach (array_filter(([
    'status' => 'unknown',
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusKey = is_object($status) && enum_exists($status::class) ? $status->value : (string) $status;

    $tone = match ($statusKey) {
        'healthy', 'success', 'active', 'connected' => [
            'dot' => 'bg-emerald-500',
            'badge' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
            'label' => $label ?? __('Healthy'),
        ],
        'warning', 'pending' => [
            'dot' => 'bg-amber-500',
            'badge' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            'label' => $label ?? __('Warning'),
        ],
        'critical', 'danger', 'stopped', 'disconnected' => [
            'dot' => 'bg-red-500',
            'badge' => 'bg-red-50 text-red-700 ring-red-600/20',
            'label' => $label ?? __('Critical'),
        ],
        default => [
            'dot' => 'bg-slate-400',
            'badge' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            'label' => $label ?? __('Unknown'),
        ],
    };
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {$tone['badge']}"])); ?>>
    <span class="h-2 w-2 shrink-0 rounded-full <?php echo e($tone['dot']); ?>" aria-hidden="true"></span>
    <span><?php echo e($tone['label']); ?></span>
</span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-status-badge.blade.php ENDPATH**/ ?>