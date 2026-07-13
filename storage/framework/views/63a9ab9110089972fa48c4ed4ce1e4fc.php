<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'workspaces' => [],
    'active' => null,
    'ariaLabel' => __('Secondary workspaces'),
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
    'ariaLabel' => __('Secondary workspaces'),
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalca8738806c125e2ad536fa5b41489349 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca8738806c125e2ad536fa5b41489349 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-pill-tabs','data' => ['workspaces' => $workspaces,'active' => $active,'variant' => 'secondary','ariaLabel' => $ariaLabel,'attributes' => $attributes->except(['workspaces', 'active', 'ariaLabel'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-pill-tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workspaces),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($active),'variant' => 'secondary','aria-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ariaLabel),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->except(['workspaces', 'active', 'ariaLabel']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca8738806c125e2ad536fa5b41489349)): ?>
<?php $attributes = $__attributesOriginalca8738806c125e2ad536fa5b41489349; ?>
<?php unset($__attributesOriginalca8738806c125e2ad536fa5b41489349); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca8738806c125e2ad536fa5b41489349)): ?>
<?php $component = $__componentOriginalca8738806c125e2ad536fa5b41489349; ?>
<?php unset($__componentOriginalca8738806c125e2ad536fa5b41489349); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/workspace-sub-tabs.blade.php ENDPATH**/ ?>