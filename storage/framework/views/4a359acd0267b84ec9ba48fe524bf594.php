<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'count' => null,
    'statusLabel' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
    'domainLabel' => null,
    'compact' => true,
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
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'count' => null,
    'statusLabel' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
    'domainLabel' => null,
    'compact' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusClasses = match ($statusVariant ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    use App\Support\Navigation\WorkspaceEmbed;

    $shellClasses = 'group relative flex h-full w-full min-w-0 min-h-[6.75rem] rounded-lg border bg-white p-2.5 transition-colors';
    $interactiveClasses = 'hover:border-erp-accent/30 hover:bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-erp-accent/25 focus:ring-offset-1';
    $disabledClasses = 'border-dashed border-erp-border/80 bg-erp-page/40 opacity-80';
    $enabledClasses = 'border-erp-border shadow-sm';
    $resolvedHref = filled($href) ? WorkspaceEmbed::url($href) : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<?php if($comingSoon || empty($href)): ?>
    <div
        <?php echo e($attributes->merge(['class' => "{$shellClasses} {$disabledClasses}"])); ?>

        aria-disabled="true"
    >
        <?php echo $__env->make('admin.settings.partials.settings-tile-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php else: ?>
    <a
        href="<?php echo e($resolvedHref); ?>"
        data-turbo-action="advance"
        data-turbo-frame="<?php echo e($turboFrame); ?>"
        <?php echo e($attributes->merge(['class' => "{$shellClasses} {$enabledClasses} {$interactiveClasses}"])); ?>

    >
        <?php echo $__env->make('admin.settings.partials.settings-tile-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\settings-tile.blade.php ENDPATH**/ ?>