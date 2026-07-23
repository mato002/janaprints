<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
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
    'variant' => 'default',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = match ($variant) {
        'success', 'active', 'completed', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning', 'pending', 'pending_approval' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger', 'inactive', 'rejected', 'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
        'info', 'in_production', 'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'draft', 'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    };
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}"])); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\status-badge.blade.php ENDPATH**/ ?>