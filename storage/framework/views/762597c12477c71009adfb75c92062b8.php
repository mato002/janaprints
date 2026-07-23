<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'percent' => 0,
    'variant' => 'neutral',
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
    'percent' => 0,
    'variant' => 'neutral',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $barClasses = match ($variant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'draft' => 'bg-slate-400',
        default => 'bg-slate-300',
    };
    $width = min(100, max(0, abs($percent)));
?>

<div class="flex min-w-[5rem] items-center gap-2">
    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
        <div class="<?php echo e($barClasses); ?> h-full rounded-full transition-all" style="width: <?php echo e($width); ?>%"></div>
    </div>
    <span class="w-12 shrink-0 text-right text-xs tabular-nums text-slate-600"><?php echo e(number_format($percent, 1)); ?>%</span>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\margin-bar.blade.php ENDPATH**/ ?>