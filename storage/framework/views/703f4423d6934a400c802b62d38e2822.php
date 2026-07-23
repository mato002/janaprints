<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Leave Request'),'breadcrumbs' => [['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => $request->reference ?? __('Request')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-erp-primary"><?php echo e($request->employee?->full_name); ?></h2>
                    <p class="text-sm text-slate-600"><?php echo e($request->leaveType?->name); ?> · <?php echo e($request->reference); ?></p>
                </div>
                <span class="erp-badge erp-badge--<?php echo e($request->status?->badgeClass()); ?>"><?php echo e($request->status?->label()); ?></span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Period')); ?></dt><dd class="font-medium"><?php echo e($request->start_date?->format('M j, Y')); ?> – <?php echo e($request->end_date?->format('M j, Y')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Days requested')); ?></dt><dd class="font-medium"><?php echo e($request->days_requested); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($request->branch?->name); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Department')); ?></dt><dd><?php echo e($request->department?->name ?? '—'); ?></dd></div>
                <div class="col-span-2"><dt class="text-slate-500"><?php echo e(__('Reason')); ?></dt><dd><?php echo e($request->reason); ?></dd></div>
            </dl>

            <?php if(! empty($request->conflict_warnings)): ?>
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold"><?php echo e(__('Conflict warnings')); ?></p>
                    <ul class="mt-1 list-disc pl-5">
                        <?php $__currentLoopData = $request->conflict_warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($warning['message'] ?? ''); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($request->rejection_reason): ?>
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                    <p class="font-semibold"><?php echo e(__('Rejection reason')); ?></p>
                    <p><?php echo e($request->rejection_reason); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
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
                <h3 class="text-sm font-semibold text-erp-primary mb-3"><?php echo e(__('Leave balance')); ?></h3>
                <dl class="space-y-2 text-sm">
                    <?php $__currentLoopData = $balanceSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between">
                            <dt class="text-slate-500"><?php echo e(__(ucwords(str_replace('_', ' ', $key)))); ?></dt>
                            <dd class="font-medium tabular-nums"><?php echo e($value); ?></dd>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <h3 class="text-sm font-semibold text-erp-primary mb-3"><?php echo e(__('Actions')); ?></h3>
                <div class="space-y-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $request)): ?>
                        <?php if($request->status === App\Enums\LeaveRequestStatus::Submitted && $request->leaveType?->requires_supervisor_approval): ?>
                            <form method="POST" action="<?php echo e(route('admin.hr.leave.approve.supervisor', $request)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="erp-btn-primary w-full"><?php echo e(__('Supervisor approve')); ?></button>
                            </form>
                        <?php endif; ?>
                        <?php if(in_array($request->status, [App\Enums\LeaveRequestStatus::Submitted, App\Enums\LeaveRequestStatus::SupervisorApproved])): ?>
                            <form method="POST" action="<?php echo e(route('admin.hr.leave.approve.hr', $request)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="erp-btn-secondary w-full"><?php echo e(__('HR approve')); ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reject', $request)): ?>
                        <?php if(in_array($request->status, [App\Enums\LeaveRequestStatus::Submitted, App\Enums\LeaveRequestStatus::SupervisorApproved])): ?>
                            <form method="POST" action="<?php echo e(route('admin.hr.leave.reject', $request)); ?>" class="space-y-2">
                                <?php echo csrf_field(); ?>
                                <textarea name="rejection_reason" rows="2" class="erp-input w-full text-sm" placeholder="<?php echo e(__('Rejection reason')); ?>" required></textarea>
                                <button type="submit" class="erp-btn-secondary w-full text-rose-700"><?php echo e(__('Reject')); ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Hr\LeaveRequest::class)): ?>
                        <?php if(! in_array($request->status, [App\Enums\LeaveRequestStatus::Cancelled, App\Enums\LeaveRequestStatus::Rejected])): ?>
                            <form method="POST" action="<?php echo e(route('admin.hr.leave.cancel', $request)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Cancel this leave request?'))->toHtml() ?>)">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="erp-btn-secondary w-full"><?php echo e(__('Cancel request')); ?></button>
                            </form>
                        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\leave\show.blade.php ENDPATH**/ ?>