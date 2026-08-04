<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'href' => null,
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
    'label',
    'value',
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedHref = $href ? (WorkspaceEmbed::url($href) ?? $href) : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<?php if($resolvedHref): ?>
    <a href="<?php echo e($resolvedHref); ?>" data-turbo-frame="<?php echo e($turboFrame); ?>" data-turbo-action="advance" <?php echo e($attributes->merge(['class' => 'exec-health-chip exec-health-chip--link'])); ?>>
<?php else: ?>
    <span <?php echo e($attributes->merge(['class' => 'exec-health-chip'])); ?>>
<?php endif; ?>
    <span class="exec-health-chip__label"><?php echo e($label); ?></span>
    <span class="exec-health-chip__value"><?php echo e($value); ?></span>
<?php if($resolvedHref): ?>
    </a>
<?php else: ?>
    </span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\exec-health-chip.blade.php ENDPATH**/ ?>