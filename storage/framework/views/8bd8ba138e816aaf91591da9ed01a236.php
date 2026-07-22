<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'workspaces' => [],
    'active' => null,
    'variant' => 'primary',
    'ariaLabel' => __('Workspaces'),
    'turboFrame' => null,
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
    'workspaces' => [],
    'active' => null,
    'variant' => 'primary',
    'ariaLabel' => __('Workspaces'),
    'turboFrame' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $turboFrame = $turboFrame ?? ($variant === 'secondary' ? 'module-workspace-content' : 'erp-main');
?>

<?php if(count($workspaces) > 0): ?>
    <nav
        <?php echo e($attributes->merge([
            'class' => 'workspace-pill-tabs module-workspace-switcher module-workspace-switcher--' . $variant,
        ])); ?>

        aria-label="<?php echo e($ariaLabel); ?>"
    >
        <div class="workspace-pill-tabs__track module-workspace-switcher__track" role="tablist">
            <?php $__currentLoopData = $workspaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $workspace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = ($active['key'] ?? null) === ($workspace['key'] ?? null);
                    $searchLabel = strtolower(implode(' ', array_filter([
                        $workspace['label'] ?? '',
                        $workspace['description'] ?? '',
                        $workspace['key'] ?? '',
                    ])));
                    $isDisabled = ! empty($workspace['coming_soon']) || empty($workspace['href']);
                ?>

                <?php if($isDisabled): ?>
                    <span
                        role="tab"
                        data-workspace-tab
                        data-search-label="<?php echo e($searchLabel); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'workspace-pill',
                            'workspace-pill--' . $variant,
                            'workspace-pill--disabled',
                        ]); ?>"
                        title="<?php echo e(__('Coming soon')); ?>"
                    >
                        <span class="workspace-pill__label"><?php echo e($workspace['label']); ?></span>
                    </span>
                <?php else: ?>
                    <a
                        href="<?php echo e($workspace['href']); ?>"
                        <?php if(! empty($workspace['content_href'])): ?>
                            data-workspace-content-href="<?php echo e($workspace['content_href']); ?>"
                        <?php endif; ?>
                        data-turbo-frame="<?php echo e($workspace['turbo_frame'] ?? $turboFrame); ?>"
                        data-turbo-action="advance"
                        data-workspace-tab
                        data-workspace-tab-key="<?php echo e($workspace['key'] ?? ''); ?>"
                        data-search-label="<?php echo e($searchLabel); ?>"
                        role="tab"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'workspace-pill',
                            'workspace-pill--' . $variant,
                            'workspace-pill--active' => $isActive,
                        ]); ?>"
                        <?php if($isActive): ?> aria-selected="true" <?php endif; ?>
                    >
                        <span class="workspace-pill__label"><?php echo e($workspace['label']); ?></span>
                        <?php if(! empty($workspace['badge'])): ?>
                            <span class="workspace-pill__badge"><?php echo e($workspace['badge']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\workspace-pill-tabs.blade.php ENDPATH**/ ?>