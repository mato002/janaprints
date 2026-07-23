<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Compose email'),'breadcrumbs' => [['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Compose')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Compose email')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Compose email'))]); ?>
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
    <form method="POST" action="<?php echo e(route('admin.communications.email.compose.store')); ?>" class="erp-card max-w-3xl space-y-4">
        <?php echo csrf_field(); ?>
        <div><label class="text-sm font-medium"><?php echo e(__('To')); ?></label><input type="text" name="to" class="erp-input w-full" value="<?php echo e(old('to', $to)); ?>" required placeholder="email@example.com"></div>
        <div><label class="text-sm font-medium"><?php echo e(__('CC')); ?></label><input type="text" name="cc" class="erp-input w-full" value="<?php echo e(old('cc')); ?>"></div>
        <div><label class="text-sm font-medium"><?php echo e(__('BCC')); ?></label><input type="text" name="bcc" class="erp-input w-full" value="<?php echo e(old('bcc')); ?>"></div>
        <div>
            <label class="text-sm font-medium"><?php echo e(__('Template')); ?></label>
            <select name="communication_template_id" class="erp-input w-full">
                <option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div><label class="text-sm font-medium"><?php echo e(__('Subject')); ?></label><input type="text" name="subject" class="erp-input w-full" value="<?php echo e(old('subject')); ?>" required></div>
        <div><label class="text-sm font-medium"><?php echo e(__('Body')); ?></label><textarea name="body" rows="8" class="erp-input w-full" required><?php echo e(old('body')); ?></textarea></div>
        <?php if($customer_id): ?><input type="hidden" name="customer_id" value="<?php echo e($customer_id); ?>"><?php endif; ?>
        <div class="flex gap-2">
            <button type="submit" class="erp-btn erp-btn--primary"><?php echo e(__('Send')); ?></button>
            <button type="submit" name="save_draft" value="1" class="erp-btn erp-btn--secondary"><?php echo e(__('Save draft')); ?></button>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\compose.blade.php ENDPATH**/ ?>