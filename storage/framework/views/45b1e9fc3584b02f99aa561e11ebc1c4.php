<?php $comp = $employee->compensation; ?>
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
    <?php if($comp): ?>
        <div class="mb-4 flex items-center justify-between">
            <span class="erp-badge erp-badge--<?php echo e($comp->status?->badgeClass()); ?>"><?php echo e($comp->status?->label()); ?></span>
            <span class="text-sm text-slate-500"><?php echo e(__('Effective')); ?> <?php echo e($comp->effective_from?->format('M j, Y')); ?></span>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Basic Salary')); ?></dt><dd class="font-medium"><?php echo e(number_format($comp->basic_salary, 2)); ?> <?php echo e($comp->currency); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Gross Components')); ?></dt><dd class="font-medium"><?php echo e(number_format($comp->grossComponents(), 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Payment Frequency')); ?></dt><dd><?php echo e($comp->payment_frequency?->label()); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Payroll Group')); ?></dt><dd><?php echo e($comp->payroll_group_label ?? '—'); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('House')); ?></dt><dd><?php echo e(number_format($comp->house_allowance, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Transport')); ?></dt><dd><?php echo e(number_format($comp->transport_allowance, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Medical')); ?></dt><dd><?php echo e(number_format($comp->medical_allowance, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Risk')); ?></dt><dd><?php echo e(number_format($comp->risk_allowance, 2)); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Responsibility')); ?></dt><dd><?php echo e(number_format($comp->responsibility_allowance, 2)); ?></dd></div>
        </dl>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $comp)): ?>
            <?php if($comp->status === App\Enums\CompensationStatus::PendingApproval): ?>
                <form method="POST" action="<?php echo e(route('admin.hr.compensation.approve', $comp)); ?>" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Approve compensation')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'currency-dollar','title' => __('No active compensation'),'description' => __('Assign a pay package before payroll can process this employee.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'currency-dollar','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No active compensation')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Assign a pay package before payroll can process this employee.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Hr\EmployeeCompensation::class)): ?>
            <a href="<?php echo e(route('admin.hr.compensation.edit', $employee)); ?>" class="erp-btn-primary mt-4 inline-flex"><?php echo e(__('Assign compensation')); ?></a>
        <?php endif; ?>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\employees\tabs\compensation.blade.php ENDPATH**/ ?>