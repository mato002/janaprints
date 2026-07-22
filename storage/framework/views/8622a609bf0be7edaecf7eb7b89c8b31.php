<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('head', null, []); ?> 
        <tr>
            <th><?php echo e(__('Reference')); ?></th>
            <th><?php echo e(__('Title')); ?></th>
            <th><?php echo e(__('Department')); ?></th>
            <th><?php echo e(__('Headcount')); ?></th>
            <th><?php echo e(__('Status')); ?></th>
        </tr>
     <?php $__env->endSlot(); ?>
     <?php $__env->slot('body', null, []); ?> 
        <?php $__empty_1 = true; $__currentLoopData = $requisitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requisition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="font-mono text-xs"><?php echo e($requisition->reference); ?></td>
                <td>
                    <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.recruitment.requisitions.show', $requisition))); ?>" class="font-medium text-erp-primary hover:underline">
                        <?php echo e($requisition->title); ?>

                    </a>
                </td>
                <td><?php echo e($requisition->department?->name ?? '—'); ?></td>
                <td><?php echo e($requisition->headcount); ?></td>
                <td><span class="erp-badge bg-slate-100 text-slate-700"><?php echo e($requisition->status->label()); ?></span></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="py-6 text-center text-slate-500"><?php echo e(__('No requisitions found.')); ?></td></tr>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $attributes = $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $component = $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<div class="mt-4"><?php echo e($requisitions->links()); ?></div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\recruitment\partials\workspace-requisitions.blade.php ENDPATH**/ ?>