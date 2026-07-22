<section class="space-y-4">
    <div class="ess-card">
        <h2 class="ess-section-title"><?php echo e(__('Account overview')); ?></h2>
        <dl class="ess-dl">
            <div><dt><?php echo e(__('Corporate email')); ?></dt><dd class="break-all"><?php echo e($security['corporate_email']); ?></dd></div>
            <div><dt><?php echo e(__('Account status')); ?></dt><dd><?php echo e($security['account_status']); ?></dd></div>
            <div><dt><?php echo e(__('Last login')); ?></dt><dd><?php echo e($security['last_login']?->format('d M Y H:i') ?? '—'); ?></dd></div>
        </dl>
    </div>

    <div class="ess-card">
        <h2 class="ess-section-title"><?php echo e(__('Change password')); ?></h2>
        <form method="POST" action="<?php echo e(route('ess.security.password.update')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="ess-label" for="current_password"><?php echo e(__('Current password')); ?></label>
                <input type="password" id="current_password" name="current_password" required class="ess-input w-full" autocomplete="current-password">
            </div>

            <div>
                <label class="ess-label" for="password"><?php echo e(__('New password')); ?></label>
                <input type="password" id="password" name="password" required class="ess-input w-full" autocomplete="new-password">
            </div>

            <div>
                <label class="ess-label" for="password_confirmation"><?php echo e(__('Confirm password')); ?></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="ess-input w-full" autocomplete="new-password">
            </div>

            <button type="submit" class="ess-btn ess-btn--primary w-full"><?php echo e(__('Update password')); ?></button>
        </form>
    </div>

    <div class="ess-card">
        <h2 class="ess-section-title"><?php echo e(__('Active sessions')); ?></h2>
        <ul class="space-y-2">
            <?php $__empty_1 = true; $__currentLoopData = $security['sessions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="rounded-lg border border-erp-border px-3 py-2 text-sm">
                    <p><?php echo e($session->ip_address ?? __('Unknown IP')); ?></p>
                    <p class="text-erp-muted"><?php echo e($session->last_activity_at?->diffForHumans()); ?></p>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="text-sm text-erp-muted"><?php echo e(__('No session records.')); ?></li>
            <?php endif; ?>
        </ul>

        <form method="POST" action="<?php echo e(route('ess.security.sessions.destroy-others')); ?>" class="mt-4">
            <?php echo csrf_field(); ?>
            <button type="submit" class="ess-btn ess-btn--ghost w-full"><?php echo e(__('Logout other sessions')); ?></button>
        </form>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\security.blade.php ENDPATH**/ ?>