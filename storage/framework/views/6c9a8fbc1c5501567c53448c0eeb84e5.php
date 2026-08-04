<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('New email campaign')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.email.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <form method="POST" action="<?php echo e(route('admin.communications.email.campaigns.store')); ?>" class="erp-card max-w-3xl space-y-4">
        <?php echo csrf_field(); ?>
        <div><label class="text-sm font-medium"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium"><?php echo e(__('Type')); ?></label>
            <select name="campaign_type" class="erp-input w-full">
                <?php $__currentLoopData = \App\Enums\EmailCampaignType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div><label class="text-sm font-medium"><?php echo e(__('To (comma-separated)')); ?></label><input name="to" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium"><?php echo e(__('Template')); ?></label>
            <select name="communication_template_id" class="erp-input w-full"><option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div><label class="text-sm font-medium"><?php echo e(__('Subject')); ?></label><input name="subject" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium"><?php echo e(__('Body')); ?></label><textarea name="body" rows="6" class="erp-input w-full" required></textarea></div>
        <div><label class="text-sm font-medium"><?php echo e(__('Schedule at')); ?></label><input type="datetime-local" name="scheduled_at" class="erp-input w-full"></div>
        <button type="submit" class="erp-btn erp-btn--primary"><?php echo e(__('Save campaign')); ?></button>
    </form>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\campaigns\create.blade.php ENDPATH**/ ?>