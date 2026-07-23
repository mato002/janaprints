<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Delivery audit')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.email.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e($message->subject); ?></h2>
            <p class="text-sm text-slate-500"><?php echo e($message->status->label()); ?></p>
            <pre class="mt-2 text-sm whitespace-pre-wrap"><?php echo e(app(\App\Support\Hr\PayrollConfidentialityService::class)->emailBodyForViewer($message)); ?></pre>
        </div>
        <div class="erp-card text-sm space-y-2">
            <div class="flex justify-between"><span><?php echo e(__('Sent')); ?></span><span><?php echo e($message->sent_at?->format('d M Y H:i') ?? '—'); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Delivered')); ?></span><span><?php echo e($message->delivered_at?->format('d M Y H:i') ?? '—'); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Opened')); ?></span><span><?php echo e($message->opened_at?->format('d M Y H:i') ?? '—'); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Clicked')); ?></span><span><?php echo e($message->clicked_at?->format('d M Y H:i') ?? '—'); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Bounced')); ?></span><span><?php echo e($message->bounced_at?->format('d M Y H:i') ?? '—'); ?></span></div>
            <?php if($message->failure_reason): ?><p class="text-red-600"><?php echo e($message->failure_reason); ?></p><?php endif; ?>
        </div>
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title"><?php echo e(__('Delivery events')); ?></h2>
            <ul class="mt-2 space-y-2 text-sm">
                <?php $__currentLoopData = $message->deliveryEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="border-b pb-2"><strong><?php echo e($event->event); ?></strong> · <?php echo e($event->created_at); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <?php if($message->communicationLog): ?>
                <a href="<?php echo e(route('admin.communications.logs.show', $message->communicationLog)); ?>" class="mt-3 inline-block text-erp-accent text-sm" data-turbo-frame="erp-main"><?php echo e(__('COM-4 communication log')); ?></a>
            <?php endif; ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\delivery\show.blade.php ENDPATH**/ ?>