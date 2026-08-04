<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'card',
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
    'card',
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

    $isInteractive = ! ($card['comingSoon'] ?? false) && filled($card['href'] ?? null);
    $shellClasses = 'forms-control-card group relative flex h-full min-h-[11.5rem] flex-col overflow-hidden rounded-xl border bg-white p-4 shadow-sm transition-colors';
    $enabledClasses = 'border-erp-border hover:border-erp-accent/35 hover:shadow-card-hover focus:outline-none focus:ring-2 focus:ring-erp-accent focus:ring-offset-2';
    $disabledClasses = 'border-dashed border-erp-border/80 bg-erp-page/40 opacity-85';
    $turboFrame = WorkspaceEmbed::turboFrame();
    $resolvedHref = $isInteractive ? WorkspaceEmbed::url($card['href']) : null;

    if ($isInteractive && $turboFrame === 'module-workspace-content' && $resolvedHref && ! str_contains($resolvedHref, 'embedded=1')) {
        $resolvedHref .= str_contains($resolvedHref, '?') ? '&embedded=1' : '?embedded=1';
    }
?>

<?php if($isInteractive): ?>
    <a
        href="<?php echo e($resolvedHref); ?>"
        data-turbo-action="advance"
        data-turbo-frame="<?php echo e($turboFrame); ?>"
        <?php echo e($attributes->merge(['class' => "{$shellClasses} {$enabledClasses}"])); ?>

    >
        <?php echo $__env->make('admin.settings.forms.partials.form-card-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </a>
<?php else: ?>
    <div
        <?php echo e($attributes->merge(['class' => "{$shellClasses} {$disabledClasses}"])); ?>

        aria-disabled="true"
    >
        <?php echo $__env->make('admin.settings.forms.partials.form-card-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\forms\partials\form-card.blade.php ENDPATH**/ ?>