<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'action',
    'maxWidth' => '4xl',
    'enctype' => null,
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
    'action',
    'maxWidth' => '4xl',
    'enctype' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $maxWidthClass = match ($maxWidth) {
        '5xl' => 'erp-lookup-modal--w-5xl',
        '4xl' => 'erp-lookup-modal--w-4xl',
        '3xl' => 'erp-lookup-modal--w-3xl',
        '2xl' => 'erp-lookup-modal--w-2xl',
        'md' => 'erp-lookup-modal--w-md',
        default => 'erp-lookup-modal--w-4xl',
    };
?>

<div
    class="erp-form-modal erp-lookup-modal w-full <?php echo e($maxWidthClass); ?>"
    data-erp-lookup-modal-panel
>
    <div class="erp-form-modal__header">
        <h2 id="erp-lookup-modal-title" class="erp-form-modal__title"><?php echo e($title); ?></h2>
        <button type="button" class="erp-form-modal__close" data-erp-lookup-modal-close aria-label="<?php echo e(__('Close')); ?>">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="erp-form-modal__body">
        <form
            method="POST"
            action="<?php echo e($action); ?>"
            class="space-y-4"
            data-erp-lookup-form
            <?php if($enctype): ?> enctype="<?php echo e($enctype); ?>" <?php endif; ?>
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_erp_lookup_create" value="1">
            <?php echo $__env->make('admin.partials.lookup-validation-errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo e($slot); ?>

            <div class="erp-form-modal__actions !mt-4 !pt-4">
                <button type="button" class="erp-btn-secondary" data-erp-lookup-modal-close><?php echo e(__('Cancel')); ?></button>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Save')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/lookup-nested-form.blade.php ENDPATH**/ ?>