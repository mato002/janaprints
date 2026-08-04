<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'links' => [],
    'ariaLabel' => __('Navigation'),
    'hideInWorkspace' => true,
    'variant' => 'pill',
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
    'links' => [],
    'ariaLabel' => __('Navigation'),
    'hideInWorkspace' => true,
    'variant' => 'pill',
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

    if ($hideInWorkspace && WorkspaceEmbed::rendersEmbeddedFragment()) {
        return;
    }

    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<nav
    <?php echo e($attributes->merge(['class' => $variant === 'pill' ? 'erp-card mb-4 flex flex-wrap gap-2 p-2' : 'mb-4 flex flex-wrap gap-2'])); ?>

    aria-label="<?php echo e($ariaLabel); ?>"
>
    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(! empty($link['permission']) && ! auth()->user()?->can($link['permission'])): ?>
            <?php continue; ?>
        <?php endif; ?>

        <?php
            $href = isset($link['route'])
                ? route($link['route'], $link['params'] ?? $link['query'] ?? [])
                : ($link['href'] ?? '#');
            $href = WorkspaceEmbed::url($href) ?? $href;
            $routePattern = $link['route_pattern'] ?? ($link['route'] ?? null);
            $isActive = $link['active'] ?? (
                $routePattern
                    ? request()->routeIs(is_string($routePattern) ? $routePattern.'*' : $routePattern)
                    : false
            );
            $pillClass = $variant === 'pill'
                ? 'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors'
                : 'rounded-md px-3 py-1.5 text-xs font-medium';
        ?>

        <a
            href="<?php echo e($href); ?>"
            data-turbo-frame="<?php echo e($turboFrame); ?>"
            data-turbo-action="advance"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                $pillClass,
                'bg-erp-accent text-white' => $isActive && $variant === 'pill',
                'bg-slate-900 text-white' => $isActive && $variant === 'compact',
                'text-slate-600 hover:bg-slate-50' => ! $isActive && $variant === 'pill',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => ! $isActive && $variant === 'compact',
            ]); ?>"
        >
            <?php echo e($link['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo e($slot); ?>

</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\workspace-nav.blade.php ENDPATH**/ ?>