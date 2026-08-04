<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $campaign->name] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $campaign->name,'description' => $campaign->campaign_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->campaign_code)]); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send', $campaign)): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <form method="POST" action="<?php echo e(route('admin.communications.email.campaigns.send', $campaign)); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm"><?php echo e(__('Send campaign')); ?></button>
                </form>
             <?php $__env->endSlot(); ?>
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
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title"><?php echo e(__('Message')); ?></h2>
            <p class="font-medium"><?php echo e($campaign->subject); ?></p>
            <pre class="mt-2 text-sm whitespace-pre-wrap"><?php echo e($campaign->body); ?></pre>
        </div>
        <div class="erp-card text-sm space-y-2">
            <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Status')); ?></span><span><?php echo e($campaign->status->label()); ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Recipients')); ?></span><span><?php echo e($campaign->total_recipients); ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Opened')); ?></span><span><?php echo e($campaign->opened_count); ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Bounced')); ?></span><span><?php echo e($campaign->bounced_count); ?></span></div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\campaigns\show.blade.php ENDPATH**/ ?>