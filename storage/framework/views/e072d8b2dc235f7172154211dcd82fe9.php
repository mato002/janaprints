<?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'switch-horizontal','title' => __('No queued jobs found'),'description' => __('Adjust filters or schedule jobs into production.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'switch-horizontal','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No queued jobs found')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Adjust filters or schedule jobs into production.'))]); ?>
    <?php if(auth()->user()?->can('production.view')): ?>
         <?php $__env->slot('action', null, []); ?> 
            <div class="flex flex-wrap items-center justify-center gap-2">
                <a href="<?php echo e(route('admin.production.job-cards.index')); ?>" class="erp-btn-primary text-sm" data-turbo-frame="erp-main"><?php echo e(__('View Job Cards')); ?></a>
                <?php if(auth()->user()?->can('production.scheduling.view')): ?>
                    <a href="<?php echo e(route('admin.production.scheduling.index')); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e(__('Open Scheduling')); ?></a>
                <?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\empty-state.blade.php ENDPATH**/ ?>