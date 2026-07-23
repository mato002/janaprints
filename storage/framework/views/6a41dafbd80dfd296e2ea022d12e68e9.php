<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['form', 'canManage', 'companyId', 'branchId']));

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

foreach (array_filter((['form', 'canManage', 'companyId', 'branchId']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $formsLandingUrl = route('admin.settings.forms.index', $scopeQuery);
?>

<?php if(! $canManage): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-3 border-amber-200 bg-amber-50 !p-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-3 border-amber-200 bg-amber-50 !p-3']); ?>
        <p class="text-sm text-amber-900">
            <span class="font-semibold"><?php echo e(__('View only')); ?></span>
            — <?php echo e(__('You have settings.view but need settings.manage to edit fields, save changes, or add custom fields. Ask an administrator to grant the Company Admin role or settings.manage permission.')); ?>

        </p>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php endif; ?>

<?php if($canManage): ?>
    <form
        method="post"
        action="<?php echo e(route('admin.settings.forms.update')); ?>"
        data-turbo="false"
        data-erp-form-settings
        data-erp-form-key="<?php echo e($form['form_key']); ?>"
        data-erp-form-label="<?php echo e($form['label']); ?>"
    >
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
        <input type="hidden" name="return_form" value="<?php echo e($form['form_key']); ?>">
        <input type="hidden" name="form" value="<?php echo e($form['form_key']); ?>">
        <input type="hidden" name="branch_id" value="<?php echo e($branchId ?? ''); ?>">
<?php endif; ?>

<?php echo $__env->make('admin.settings.forms.partials.workspace-panel', [
    'form' => $form,
    'canManage' => $canManage,
    'backUrl' => $formsLandingUrl,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($canManage): ?>
    </form>

    <?php echo $__env->make('admin.settings.forms.partials.form-settings-submit-script', ['formKey' => $form['form_key']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\forms\partials\workspace.blade.php ENDPATH**/ ?>