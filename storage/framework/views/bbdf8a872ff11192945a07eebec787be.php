<?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'collection','title' => __('No active production jobs found.'),'description' => __('Create a job card from a confirmed sales order or review orders ready for production.'),'dataExportSkip' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'collection','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No active production jobs found.')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create a job card from a confirmed sales order or review orders ready for production.')),'data-export-skip' => true]); ?>
     <?php $__env->slot('action', null, []); ?> 
        <div class="flex flex-wrap items-center justify-center gap-3">
            <?php if($canCreate && $createUrl): ?>
                <a href="<?php echo e($createUrl); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Create Job Card')); ?></a>
            <?php endif; ?>
            <?php if($salesOrdersUrl): ?>
                <a href="<?php echo e($salesOrdersUrl); ?>" class="erp-btn-secondary" data-turbo-frame="erp-main"><?php echo e(__('View Sales Orders')); ?></a>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\register\empty-state.blade.php ENDPATH**/ ?>