<?php if (isset($component)) { $__componentOriginal308cd3c4087636aca146ca95b542790a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal308cd3c4087636aca146ca95b542790a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-content','data' => ['frameId' => 'module-workspace-content','attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['frame-id' => 'module-workspace-content','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal308cd3c4087636aca146ca95b542790a)): ?>
<?php $attributes = $__attributesOriginal308cd3c4087636aca146ca95b542790a; ?>
<?php unset($__attributesOriginal308cd3c4087636aca146ca95b542790a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal308cd3c4087636aca146ca95b542790a)): ?>
<?php $component = $__componentOriginal308cd3c4087636aca146ca95b542790a; ?>
<?php unset($__componentOriginal308cd3c4087636aca146ca95b542790a); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\embedded-workspace-frame.blade.php ENDPATH**/ ?>