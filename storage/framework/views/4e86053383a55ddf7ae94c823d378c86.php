<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'resetUrl' => null,
    'turboFrame' => null,
    'method' => 'GET',
    'pills' => [],
    'pillParam' => 'status',
    'activePill' => null,
    'showReset' => true,
    'compact' => false,
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
    'action',
    'resetUrl' => null,
    'turboFrame' => null,
    'method' => 'GET',
    'pills' => [],
    'pillParam' => 'status',
    'activePill' => null,
    'showReset' => true,
    'compact' => false,
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

    $resolvedTurboFrame = $turboFrame ?? WorkspaceEmbed::turboFrame();
    $resolvedAction = WorkspaceEmbed::url($action) ?? $action;
    $resolvedResetUrl = $resetUrl ? (WorkspaceEmbed::url($resetUrl) ?? $resetUrl) : null;
    $embedded = WorkspaceEmbed::inWorkspaceContext();
?>

<form
    method="<?php echo e($method); ?>"
    action="<?php echo e($resolvedAction); ?>"
    <?php echo e($attributes->merge(['class' => 'erp-index-toolbar-form'])); ?>

    <?php if($resolvedTurboFrame): ?> data-turbo-frame="<?php echo e($resolvedTurboFrame); ?>" <?php endif; ?>
>
    <?php if($embedded): ?>
        <input type="hidden" name="embedded" value="1">
    <?php endif; ?>
    <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex items-center gap-2', 'erp-index-toolbar-row' => $compact]); ?>">
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex min-w-0 flex-1 items-center gap-1.5',
                'flex-nowrap' => $compact,
                'flex-wrap' => ! $compact,
            ]); ?>">
                <?php if(count($pills) > 1): ?>
                    <?php if (isset($component)) { $__componentOriginal8d71058e635815f8f51e2bf876db5ad4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pills','data' => ['options' => $pills,'param' => $pillParam,'current' => $activePill,'turboFrame' => $resolvedTurboFrame]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pills'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pills),'param' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pillParam),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activePill),'turbo-frame' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($resolvedTurboFrame)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $attributes = $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $component = $__componentOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
<?php endif; ?>
                <?php endif; ?>

                <?php echo e($slot); ?>


                <?php if($showReset): ?>
                    <button
                        type="button"
                        data-erp-filter-reset
                        class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                    ><?php echo e(__('Reset')); ?></button>
                <?php endif; ?>
            </div>

            <div class="ml-auto flex shrink-0 items-center gap-2">
                <?php if(isset($pagination)): ?>
                    <?php echo e($pagination); ?>

                <?php endif; ?>

                <?php if(isset($actions)): ?>
                    <?php echo e($actions); ?>

                <?php endif; ?>

                <?php if(isset($export)): ?>
                    <?php echo e($export); ?>

                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($secondary)): ?>
            <div class="mt-2 flex w-full flex-wrap items-center gap-2 border-t border-erp-border/60 pt-2">
                <?php echo e($secondary); ?>

            </div>
        <?php endif; ?>
    </div>
</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/index-toolbar.blade.php ENDPATH**/ ?>