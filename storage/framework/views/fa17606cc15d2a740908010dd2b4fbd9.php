<?php
    $activationStatus = $activationStatus ?? 'none';
    $latestActivation = $latestActivation ?? null;
    $assignedRole = $employee->user?->getRoleNames()->first();
?>

<div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-5">
    <h3 class="text-sm font-semibold text-gray-900"><?php echo e(__('Account activation')); ?></h3>

    <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500"><?php echo e(__('Login email')); ?></dt>
            <dd class="font-medium text-gray-900"><?php echo e($employee->email ?: '—'); ?></dd>
        </div>
        <div>
            <dt class="text-gray-500"><?php echo e(__('Activation status')); ?></dt>
            <dd class="font-medium text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $activationStatus))); ?></dd>
        </div>
        <div>
            <dt class="text-gray-500"><?php echo e(__('Intended system role')); ?></dt>
            <dd class="font-medium text-gray-900"><?php echo e($employee->activation_role ?: __('Config fallback')); ?></dd>
        </div>
        <div>
            <dt class="text-gray-500"><?php echo e(__('Assigned ERP role')); ?></dt>
            <dd class="font-medium text-gray-900"><?php echo e($assignedRole ?: '—'); ?></dd>
        </div>
        <div>
            <dt class="text-gray-500"><?php echo e(__('Last invitation sent')); ?></dt>
            <dd class="font-medium text-gray-900">
                <?php echo e($latestActivation?->last_invitation_sent_at?->format('Y-m-d H:i') ?: '—'); ?>

            </dd>
        </div>
        <div>
            <dt class="text-gray-500"><?php echo e(__('Activation expires')); ?></dt>
            <dd class="font-medium text-gray-900">
                <?php echo e($latestActivation?->expires_at?->format('Y-m-d H:i') ?: '—'); ?>

            </dd>
        </div>
    </dl>

    <?php if($employee->activation_status?->value !== 'activated' && filled($employee->email)): ?>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php if($activationStatus === 'pending'): ?>
                <form method="POST" action="<?php echo e(route('admin.employees.resend-activation', $employee)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.secondary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('secondary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Resend activation')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $attributes = $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $component = $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
                </form>
            <?php endif; ?>
            <?php if(in_array($activationStatus, ['expired', 'pending', 'none'], true)): ?>
                <form method="POST" action="<?php echo e(route('admin.employees.regenerate-activation', $employee)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.secondary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('secondary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Regenerate activation link')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $attributes = $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $component = $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('email', App\Models\Employee::class)): ?>
        <?php if(filled($employee->email)): ?>
            <div class="mt-4">
                <a
                    href="<?php echo e(url()->route('admin.employees.email.compose', ['employees' => [$employee->id]])); ?>"
                    class="erp-btn-secondary inline-flex"
                    data-erp-modal-open
                >
                    <?php echo e(__('Send email')); ?>

                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($employee->activation_status?->value === 'activated' && ! $assignedRole): ?>
        <p class="mt-4 text-sm text-amber-700">
            <?php echo e(__('Activation completed without an ERP role assignment. Assign a role from Users administration.')); ?>

        </p>
    <?php endif; ?>

    <?php echo $__env->make('admin.employees.partials.email-readiness-panel', ['readinessChecks' => $readinessChecks ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\employees\partials\email-identity-panel.blade.php ENDPATH**/ ?>