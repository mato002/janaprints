<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Email templates')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.email.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Email template bindings'),'description' => __('COM-1 email templates linked for campaigns and automation.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email template bindings')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('COM-1 email templates linked for campaigns and automation.'))]); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage', App\Models\Communications\EmailCampaign::class)): ?>
             <?php $__env->slot('actions', null, []); ?> <form method="POST" action="<?php echo e(route('admin.communications.email.templates.sync')); ?>"><?php echo csrf_field(); ?><button class="erp-btn erp-btn--secondary erp-btn--sm"><?php echo e(__('Sync COM-1')); ?></button></form> <?php $__env->endSlot(); ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
    <div class="erp-card mb-4 overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead><tr><th><?php echo e(__('ERP event')); ?></th><th><?php echo e(__('Category')); ?></th><th><?php echo e(__('COM-1 template')); ?></th><th><?php echo e(__('Binding')); ?></th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $automationMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['event']->label()); ?></td>
                        <td><?php echo e($row['category_label']); ?></td>
                        <td><?php echo e($row['template']?->name ?? '—'); ?></td>
                        <td><?php echo e($row['binding'] ? __('Linked') : '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th><?php echo e(__('Template')); ?></th><th><?php echo e(__('Automation')); ?></th><th><?php echo e(__('Active')); ?></th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bindings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $binding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($binding->communicationTemplate->name); ?></td>
                        <td><?php echo e($binding->automation_event?->label() ?? '—'); ?></td>
                        <td><?php echo e($binding->is_active ? __('Yes') : __('No')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="py-6 text-center text-slate-500"><?php echo e(__('Sync COM-1 email-channel templates first.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\templates\index.blade.php ENDPATH**/ ?>