<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['customer', 'orderId', 'orderNumber']));

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

foreach (array_filter((['customer', 'orderId', 'orderNumber']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\SalesOrder::class)): ?>
    <form
        method="POST"
        action="<?php echo e(route('admin.crm.customers.repeat-order', [$customer, $orderId])); ?>"
        class="inline"
        onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Create a repeat order from :number?', ['number' => $orderNumber]))->toHtml() ?>)"
    >
        <?php echo csrf_field(); ?>
        <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'sm','type' => 'submit','class' => 'min-h-[2.25rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','type' => 'submit','class' => 'min-h-[2.25rem]']); ?>
            <?php echo e(__('Repeat Order')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
    </form>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/360/partials/repeat-order-form.blade.php ENDPATH**/ ?>