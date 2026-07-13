<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('SMS Queue'),'breadcrumbs' => [['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Queue')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.sms.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('SMS message queue')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SMS message queue'))]); ?>
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

    <form method="GET" class="erp-card mb-4 flex flex-wrap gap-2" data-turbo-frame="erp-main">
        <select name="queue_status" class="erp-input erp-input--sm" onchange="this.form.submit()">
            <option value=""><?php echo e(__('All queue statuses')); ?></option>
            <?php $__currentLoopData = \App\Enums\SmsMessageQueueStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->value); ?>" <?php if(request('queue_status') === $s->value): echo 'selected'; endif; ?>><?php echo e($s->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="delivery_status" class="erp-input erp-input--sm" onchange="this.form.submit()">
            <option value=""><?php echo e(__('All delivery statuses')); ?></option>
            <?php $__currentLoopData = \App\Enums\SmsDeliveryStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->value); ?>" <?php if(request('delivery_status') === $s->value): echo 'selected'; endif; ?>><?php echo e($s->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>

    <div class="erp-card overflow-hidden p-0">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Campaign')); ?></th>
                    <th><?php echo e(__('Phone')); ?></th>
                    <th><?php echo e(__('Queue')); ?></th>
                    <th><?php echo e(__('Delivery')); ?></th>
                    <th><?php echo e(__('Segments')); ?></th>
                    <th><?php echo e(__('Attempts')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($message->campaign?->name); ?></td>
                        <td class="font-mono text-xs"><?php echo e($message->phone_number); ?></td>
                        <td><?php echo e($message->queue_status->label()); ?></td>
                        <td><?php echo e($message->delivery_status?->label() ?? '—'); ?></td>
                        <td class="tabular-nums"><?php echo e($message->segments_count); ?></td>
                        <td class="tabular-nums"><?php echo e($message->attempts); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="py-8 text-center text-slate-500"><?php echo e(__('No messages in queue.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if($messages->hasPages()): ?>
            <div class="border-t px-4 py-3"><?php echo e($messages->links()); ?></div>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/sms/queues/index.blade.php ENDPATH**/ ?>