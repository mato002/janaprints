<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $template->name,'breadcrumbs' => [
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting templates'), 'url' => route('admin.accounting.posting.templates.index')],
    ['label' => $template->code],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $template->name,'description' => $template->description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($template->name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($template->description)]); ?>
         <?php $__env->slot('meta', null, []); ?> 
            <span class="erp-badge"><?php echo e($template->module->label()); ?></span>
            <?php if($template->is_system): ?><span class="erp-badge"><?php echo e(__('System')); ?></span><?php endif; ?>
            <?php if(! $template->is_active): ?><span class="erp-badge"><?php echo e(__('Inactive')); ?></span><?php endif; ?>
         <?php $__env->endSlot(); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage', $template)): ?>
            <div class="flex gap-2">
                <?php if (! ($template->is_system)): ?>
                    <a href="<?php echo e(route('admin.accounting.posting.templates.edit', $template)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('admin.accounting.posting.templates.toggle', $template)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="erp-btn-secondary"><?php echo e($template->is_active ? __('Deactivate') : __('Activate')); ?></button>
                </form>
            </div>
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

    <div class="erp-card">
        <h2 class="erp-card-title"><?php echo e(__('Template lines')); ?></h2>
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo e(__('Side')); ?></th>
                        <th><?php echo e(__('Account')); ?></th>
                        <th><?php echo e(__('Amount')); ?></th>
                        <th><?php echo e(__('Description')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $template->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-sm"><?php echo e($line->line_number); ?></td>
                            <td class="text-sm capitalize"><?php echo e($line->entry_side->value); ?></td>
                            <td class="text-sm">
                                <?php echo e($line->account_resolver->label()); ?>

                                <?php if($line->account_key): ?>
                                    <span class="font-mono text-xs text-slate-500">(<?php echo e($line->account_key); ?>)</span>
                                <?php endif; ?>
                                <?php if($line->glAccount): ?>
                                    — <?php echo e($line->glAccount->code); ?> <?php echo e($line->glAccount->name); ?>

                                <?php endif; ?>
                            </td>
                            <td class="text-sm"><?php echo e($line->amount_source->label()); ?></td>
                            <td class="text-sm text-slate-600"><?php echo e($line->line_description); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\posting\templates\show.blade.php ENDPATH**/ ?>