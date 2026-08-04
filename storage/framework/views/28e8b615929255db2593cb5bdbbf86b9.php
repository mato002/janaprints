<?php $__env->startSection('content'); ?>
    <h2 style="margin:0 0 16px;color:#0f1b3d;font-size:20px;"><?php echo e(__('Reset your password')); ?></h2>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        <?php echo e(__('Hello :name,', ['name' => $userName])); ?>

    </p>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        <?php echo e(__('We received a request to reset the password for your :portal account. Click the button below to choose a new password.', ['portal' => $portalLabel])); ?>

    </p>

    <p style="margin:0 0 20px;text-align:center;">
        <a href="<?php echo e($url); ?>" style="display:inline-block;background:#ff7a18;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:600;font-size:15px;">
            <?php echo e(__('Reset password')); ?>

        </a>
    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('This link expires in :count minutes.', ['count' => $expireMinutes])); ?>

    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('If you did not request a password reset, you can safely ignore this email.')); ?>

    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('If the button does not work, copy and paste this URL into your browser:')); ?><br>
        <a href="<?php echo e($url); ?>" style="color:#e91e8c;word-break:break-all;"><?php echo e($url); ?></a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('mail.layouts.jana', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\auth\reset-password.blade.php ENDPATH**/ ?>