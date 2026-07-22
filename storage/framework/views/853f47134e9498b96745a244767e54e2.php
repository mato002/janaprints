<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('WhatsApp Templates'),'breadcrumbs' => [['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Templates')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.whatsapp.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('WhatsApp templates'),'description' => __('Bindings to COM-1 templates — automation-ready, no auto-send yet.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('WhatsApp templates')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Bindings to COM-1 templates — automation-ready, no auto-send yet.'))]); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage', App\Models\Communications\WhatsappConversation::class)): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <form method="POST" action="<?php echo e(route('admin.communications.whatsapp.templates.sync')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm"><?php echo e(__('Sync from COM-1')); ?></button>
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

    <div class="erp-card mb-4">
        <h2 class="erp-card-title"><?php echo e(__('Automation event mapping')); ?></h2>
        <p class="text-xs text-slate-500 mb-3"><?php echo e(__('Prepared for future workflows — sending is not enabled.')); ?></p>
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('ERP event')); ?></th>
                    <th><?php echo e(__('COM-1 category')); ?></th>
                    <th><?php echo e(__('Active template')); ?></th>
                    <th><?php echo e(__('Binding')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $automationMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['event']->label()); ?></td>
                        <td><?php echo e($row['category_label']); ?></td>
                        <td><?php echo e($row['template']?->name ?? '—'); ?></td>
                        <td><?php echo e($row['binding'] ? __('Linked') : __('Not linked')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="erp-card">
        <h2 class="erp-card-title"><?php echo e(__('Template bindings')); ?></h2>
        <table class="erp-table w-full mt-2">
            <thead>
                <tr>
                    <th><?php echo e(__('Template')); ?></th>
                    <th><?php echo e(__('Automation')); ?></th>
                    <th><?php echo e(__('Account')); ?></th>
                    <th><?php echo e(__('Active')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bindings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $binding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($binding->communicationTemplate->name); ?> <span class="text-xs text-slate-400">(<?php echo e($binding->communicationTemplate->code); ?>)</span></td>
                        <td><?php echo e($binding->automation_event?->label() ?? '—'); ?></td>
                        <td><?php echo e($binding->account?->name ?? __('Any')); ?></td>
                        <td><?php echo e($binding->is_active ? __('Yes') : __('No')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="py-6 text-center text-slate-500"><?php echo e(__('No bindings. Sync COM-1 WhatsApp templates or create templates with channel WhatsApp.')); ?></td></tr>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\whatsapp\templates\index.blade.php ENDPATH**/ ?>