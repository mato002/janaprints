<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'icon' => 'home',
    'href' => null,
    'badge' => null,
    'comingSoon' => false,
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
    'description' => null,
    'icon' => 'home',
    'href' => null,
    'badge' => null,
    'comingSoon' => false,
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

    // Module desk cards always navigate the outer shell (section changes / desk hops).
    $resolvedHref = $href ? WorkspaceEmbed::mainUrl($href) : null;
?>

<a
    <?php if($resolvedHref && ! $comingSoon): ?>
        href="<?php echo e($resolvedHref); ?>"
        data-turbo-frame="erp-main"
        data-turbo-action="advance"
    <?php endif; ?>
    <?php echo e($attributes->merge([
        'class' => 'module-workspace-card'.($comingSoon || ! $resolvedHref ? ' module-workspace-card--disabled' : ''),
    ])); ?>

    <?php if($comingSoon || ! $resolvedHref): ?> aria-disabled="true" tabindex="-1" <?php endif; ?>
>
    <span class="module-workspace-card__icon">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
    </span>
    <span class="module-workspace-card__body">
        <span class="module-workspace-card__title"><?php echo e($title); ?></span>
        <?php if($description): ?>
            <span class="module-workspace-card__description"><?php echo e($description); ?></span>
        <?php endif; ?>
    </span>
    <?php if($badge): ?>
        <span class="module-workspace-card__badge"><?php echo e($badge); ?></span>
    <?php endif; ?>
</a>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\workspace-card.blade.php ENDPATH**/ ?>