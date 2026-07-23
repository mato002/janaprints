<?php if (isset($component)) { $__componentOriginala826d696a1cd5f6e2881b0defe272cb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala826d696a1cd5f6e2881b0defe272cb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-nested-form','data' => ['title' => $title,'action' => $action,'maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-nested-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'max-width' => '4xl']); ?>
    <?php echo $__env->make('admin.crm.customers.form', ['customer' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala826d696a1cd5f6e2881b0defe272cb0)): ?>
<?php $attributes = $__attributesOriginala826d696a1cd5f6e2881b0defe272cb0; ?>
<?php unset($__attributesOriginala826d696a1cd5f6e2881b0defe272cb0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala826d696a1cd5f6e2881b0defe272cb0)): ?>
<?php $component = $__componentOriginala826d696a1cd5f6e2881b0defe272cb0; ?>
<?php unset($__componentOriginala826d696a1cd5f6e2881b0defe272cb0); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/lookups/quick-create/customer.blade.php ENDPATH**/ ?>