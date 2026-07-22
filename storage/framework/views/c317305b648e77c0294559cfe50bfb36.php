<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Cash Reconciliation Detail')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $reconciliation->reconciliation_number,'description' => __('Session :session', ['session' => $reconciliation->session?->session_number ?? '—'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reconciliation->reconciliation_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Session :session', ['session' => $reconciliation->session?->session_number ?? '—']))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.commercial.pos.reconciliation.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Back to dashboard')); ?></a>
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

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-5">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Opening Float'),'value' => number_format($reconciliation->opening_float, 2),'icon' => 'cash']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Opening Float')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($reconciliation->opening_float, 2)),'icon' => 'cash']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Cash Sales'),'value' => number_format($reconciliation->cash_sales, 2),'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Cash Sales')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($reconciliation->cash_sales, 2)),'icon' => 'currency-dollar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('M-Pesa Sales'),'value' => number_format($reconciliation->mpesa_sales, 2),'icon' => 'device-mobile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('M-Pesa Sales')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($reconciliation->mpesa_sales, 2)),'icon' => 'device-mobile']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Card Sales'),'value' => number_format($reconciliation->card_sales, 2),'icon' => 'credit-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Card Sales')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($reconciliation->card_sales, 2)),'icon' => 'credit-card']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Refunds'),'value' => $reconciliation->refunds_count,'icon' => 'switch-horizontal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Refunds')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reconciliation->refunds_count),'icon' => 'switch-horizontal']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Cash Summary')); ?></h2>
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Cashier')); ?></dt><dd><?php echo e($reconciliation->cashier?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($reconciliation->branch?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Expected Cash')); ?></dt><dd class="tabular-nums font-semibold"><?php echo e(number_format($reconciliation->expected_cash, 2)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Actual Cash')); ?></dt><dd class="tabular-nums font-semibold"><?php echo e(number_format($reconciliation->actual_cash, 2)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Variance')); ?></dt><dd class="tabular-nums font-semibold"><?php echo e(number_format($reconciliation->variance, 2)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Variance Type')); ?></dt><dd><?php echo e(ucfirst($reconciliation->variance_type->value)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php echo e(ucfirst(str_replace('_', ' ', $reconciliation->status->value))); ?></dd></div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Approval Workflow')); ?></h2>
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Submitted')); ?></dt><dd><?php echo e($reconciliation->submitted_at?->format('d M Y H:i') ?? '—'); ?> <?php echo e($reconciliation->submitter?->name ? '· '.$reconciliation->submitter->name : ''); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Reviewed')); ?></dt><dd><?php echo e($reconciliation->reviewed_at?->format('d M Y H:i') ?? '—'); ?> <?php echo e($reconciliation->reviewer?->name ? '· '.$reconciliation->reviewer->name : ''); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Approved')); ?></dt><dd><?php echo e($reconciliation->approved_at?->format('d M Y H:i') ?? '—'); ?> <?php echo e($reconciliation->approver?->name ? '· '.$reconciliation->approver->name : ''); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Rejected')); ?></dt><dd><?php echo e($reconciliation->rejected_at?->format('d M Y H:i') ?? '—'); ?> <?php echo e($reconciliation->rejector?->name ? '· '.$reconciliation->rejector->name : ''); ?></dd></div>
            </dl>

            <div class="mt-6 space-y-4">
                <?php if($can_submit && $reconciliation->status === App\Enums\PosReconciliationStatus::Pending): ?>
                    <form method="POST" action="<?php echo e(route('admin.commercial.pos.reconciliation.submit', $reconciliation)); ?>" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-[11px] text-slate-500" for="notes"><?php echo e(__('Submission Notes')); ?></label>
                            <textarea id="notes" name="notes" rows="2" class="erp-input mt-1 w-full"><?php echo e(old('notes', $reconciliation->notes)); ?></textarea>
                        </div>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Submit reconciliation')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($can_review && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at === null): ?>
                    <form method="POST" action="<?php echo e(route('admin.commercial.pos.reconciliation.review', $reconciliation)); ?>" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-[11px] text-slate-500" for="review_notes"><?php echo e(__('Supervisor Review Notes')); ?></label>
                            <textarea id="review_notes" name="review_notes" rows="2" class="erp-input mt-1 w-full"><?php echo e(old('review_notes')); ?></textarea>
                        </div>
                        <button type="submit" class="erp-btn-secondary"><?php echo e(__('Mark as reviewed')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($can_approve && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at !== null): ?>
                    <form method="POST" action="<?php echo e(route('admin.commercial.pos.reconciliation.approve', $reconciliation)); ?>" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-[11px] text-slate-500" for="approval_notes"><?php echo e(__('Approval Notes')); ?></label>
                            <textarea id="approval_notes" name="approval_notes" rows="2" class="erp-input mt-1 w-full"><?php echo e(old('approval_notes')); ?></textarea>
                        </div>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Approve reconciliation')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($can_reject && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at !== null): ?>
                    <form method="POST" action="<?php echo e(route('admin.commercial.pos.reconciliation.reject', $reconciliation)); ?>" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-[11px] text-slate-500" for="rejection_reason"><?php echo e(__('Rejection Reason')); ?></label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="2" class="erp-input mt-1 w-full" required><?php echo e(old('rejection_reason')); ?></textarea>
                        </div>
                        <button type="submit" class="erp-btn-secondary text-rose-700"><?php echo e(__('Reject reconciliation')); ?></button>
                    </form>
                <?php endif; ?>
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

    <?php if($can_audit && $logs->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Audit Trail')); ?></h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2"><?php echo e(__('When')); ?></th>
                            <th class="px-3 py-2"><?php echo e(__('User')); ?></th>
                            <th class="px-3 py-2"><?php echo e(__('Action')); ?></th>
                            <th class="px-3 py-2"><?php echo e(__('Notes')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-erp-border/60">
                                <td class="px-3 py-2"><?php echo e($log->created_at?->format('d M Y H:i')); ?></td>
                                <td class="px-3 py-2"><?php echo e($log->user?->name ?? '—'); ?></td>
                                <td class="px-3 py-2"><?php echo e(ucfirst($log->action->value)); ?></td>
                                <td class="px-3 py-2"><?php echo e($log->notes ?? '—'); ?></td>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\reconciliation\show.blade.php ENDPATH**/ ?>