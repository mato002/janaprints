<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'method' => 'POST',
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
    'method' => 'POST',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inFormModal = request()->header('Turbo-Frame') === 'erp-form-modal';
    $httpMethod = strtoupper($method);
?>

<form
    method="<?php echo e($httpMethod === 'GET' ? 'GET' : 'POST'); ?>"
    action="<?php echo e($action); ?>"
    <?php if($inFormModal): ?> data-turbo="false" <?php else: ?> data-turbo-frame="erp-main" data-turbo-action="advance" <?php endif; ?>
    <?php echo e($attributes->merge(['class' => 'erp-form-shell'])); ?>

>
    <?php echo csrf_field(); ?>
    <?php if($inFormModal): ?>
        <input type="hidden" name="_erp_modal" value="1">
        <input type="hidden" name="_erp_modal_return" value="<?php echo e(url()->current()); ?>">
    <?php endif; ?>
    <?php if(! in_array($httpMethod, ['GET', 'POST'], true)): ?>
        <?php echo method_field($method); ?>
    <?php endif; ?>
    <?php echo $__env->make('admin.partials.modal-validation-alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo e($slot); ?>

</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\form-shell.blade.php ENDPATH**/ ?>