<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $exit->reference,'breadcrumbs' => [['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Exit Management'), 'url' => route('admin.hr.exit.dashboard')], ['label' => $exit->reference]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $exit->reference,'description' => $exit->employee->full_name.' · '.$exit->exit_type->label()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exit->reference),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exit->employee->full_name.' · '.$exit->exit_type->label())]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.hr.exit.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Back')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $exit)): ?>
                <?php if($exit->status->value === 'clearance_complete'): ?>
                    <form method="POST" action="<?php echo e(route('admin.hr.exit.settle', $exit)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Settle final dues')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if($exit->status->value === 'settled'): ?>
                    <form method="POST" action="<?php echo e(route('admin.hr.exit.close', $exit)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Close exit and deactivate employee?'))->toHtml() ?>)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-secondary"><?php echo e(__('Close exit')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
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
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1','title' => __('Exit Summary')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Exit Summary'))]); ?>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Employee')); ?></dt><dd><?php echo e($exit->employee->full_name); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php echo e($exit->status->label()); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Last Working Date')); ?></dt><dd><?php echo e($exit->last_working_date->format('Y-m-d')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Exit Date')); ?></dt><dd><?php echo e($exit->exit_date->format('Y-m-d')); ?></dd></div>
                <?php $progress = $exit->clearanceProgress() ?>
                <div><dt class="text-slate-500"><?php echo e(__('Clearance')); ?></dt><dd><?php echo e($progress['done']); ?> / <?php echo e($progress['total']); ?></dd></div>
            </dl>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-2','title' => __('Final Dues')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Final Dues'))]); ?>
            <div class="grid gap-3 sm:grid-cols-2">
                <?php $__currentLoopData = [
                    ['label' => __('Leave Balance (days)'), 'value' => number_format($exit->leave_balance_days, 1)],
                    ['label' => __('Leave Payout'), 'value' => number_format($exit->leave_balance_amount, 2)],
                    ['label' => __('Salary Balance'), 'value' => number_format($exit->salary_balance, 2)],
                    ['label' => __('Deductions'), 'value' => number_format($exit->deductions_total, 2)],
                    ['label' => __('Net Final Dues'), 'value' => number_format($exit->net_final_dues, 2)],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e($item['label']); ?></p>
                        <p class="mt-1 text-lg font-semibold"><?php echo e($item['value']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4','title' => __('Clearance Checklist')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Clearance Checklist'))]); ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="py-2 pr-3"><?php echo e(__('Department')); ?></th>
                        <th class="py-2 pr-3"><?php echo e(__('Status')); ?></th>
                        <th class="py-2 pr-3"><?php echo e(__('Cleared By')); ?></th>
                        <th class="py-2 pr-3"><?php echo e(__('Date')); ?></th>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $exit)): ?>
                            <?php if(! in_array($exit->status->value, ['settled', 'closed'])): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $exit->clearances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clearance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-3 font-medium"><?php echo e($clearance->category->label()); ?></td>
                            <td class="py-2 pr-3"><?php echo e($clearance->status->label()); ?></td>
                            <td class="py-2 pr-3"><?php echo e($clearance->clearedBy?->name ?? '—'); ?></td>
                            <td class="py-2 pr-3"><?php echo e($clearance->cleared_at?->format('Y-m-d') ?? '—'); ?></td>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $exit)): ?>
                                <?php if(! in_array($exit->status->value, ['settled', 'closed']) && $clearance->status->value === 'pending'): ?>
                                    <td class="py-2">
                                        <form method="POST" action="<?php echo e(route('admin.hr.exit.clearance', [$exit, $clearance])); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="cleared">
                                            <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Clear')); ?></button>
                                        </form>
                                    </td>
                                <?php elseif(! in_array($exit->status->value, ['settled', 'closed'])): ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    <?php if($exit->reason || $exit->notes): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4','title' => __('Notes')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Notes'))]); ?>
            <?php if($exit->reason): ?><p class="text-sm"><strong><?php echo e(__('Reason')); ?>:</strong> <?php echo e($exit->reason); ?></p><?php endif; ?>
            <?php if($exit->notes): ?><p class="mt-2 text-sm"><strong><?php echo e(__('Notes')); ?>:</strong> <?php echo e($exit->notes); ?></p><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\exit\show.blade.php ENDPATH**/ ?>