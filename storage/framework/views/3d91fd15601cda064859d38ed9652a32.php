<?php
    use App\Enums\UserSessionStatus;
?>

<section>
    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Authentication'),'description' => __('How this account is identified and signed in.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Authentication')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('How this account is identified and signed in.'))]); ?>
        <div class="md:col-span-2 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Account status')); ?></p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    <?php echo e($user->is_active ? __('Active') : __('Inactive')); ?>

                </p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Email verification')); ?></p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    <?php if($user->email_verified_at): ?>
                        <?php echo e(__('Verified')); ?>

                        <span class="block text-xs font-normal text-slate-500"><?php echo e($user->email_verified_at->format('M j, Y g:i A')); ?></span>
                    <?php else: ?>
                        <?php echo e(__('Not verified')); ?>

                    <?php endif; ?>
                </p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Company')); ?></p>
                <p class="mt-1 text-sm font-semibold text-erp-primary"><?php echo e($user->company?->name ?? '—'); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Default branch')); ?></p>
                <p class="mt-1 text-sm font-semibold text-erp-primary"><?php echo e($user->defaultBranch?->name ?? '—'); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Linked employee')); ?></p>
                <p class="mt-1 text-sm font-semibold text-erp-primary">
                    <?php if($user->employee): ?>
                        <?php echo e($user->employee->full_name); ?>

                        <span class="font-normal text-slate-500">(<?php echo e($user->employee->employee_number); ?>)</span>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </p>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\profile\partials\authentication-summary.blade.php ENDPATH**/ ?>