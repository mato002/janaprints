<?php $__env->startSection('content'); ?>
    <h2 style="margin:0 0 16px;color:#0f1b3d;font-size:20px;"><?php echo e(__('Welcome to :company', ['company' => $companyName ?? 'Jana Prints'])); ?></h2>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        <?php echo e(__('Hello :name,', ['name' => $employeeName])); ?>

    </p>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        <?php echo e(__('Your employee account has been created. Use the button below to set your password and access the ERP.')); ?>

    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 8px;color:#64748b;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;"><?php echo e(__('Login email')); ?></p>
                <p style="margin:0;color:#0f1b3d;font-size:16px;font-weight:600;"><?php echo e($loginEmail); ?></p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 20px;text-align:center;">
        <a href="<?php echo e($activationUrl); ?>" style="display:inline-block;background:linear-gradient(135deg,#e91e8c,#ff6b35);color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:600;font-size:15px;">
            <?php echo e(__('Activate your account')); ?>

        </a>
    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('Activation link expires on :date.', ['date' => $expiresAtFormatted])); ?>

    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('If the button does not work, copy and paste this URL into your browser:')); ?><br>
        <a href="<?php echo e($activationUrl); ?>" style="color:#e91e8c;word-break:break-all;"><?php echo e($activationUrl); ?></a>
    </p>

    <p style="margin:16px 0 0;color:#64748b;font-size:13px;line-height:1.6;">
        <?php echo e(__('Need help? Contact support at :email.', ['email' => $supportEmail])); ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('mail.layouts.jana', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/mail/employee-onboarding.blade.php ENDPATH**/ ?>