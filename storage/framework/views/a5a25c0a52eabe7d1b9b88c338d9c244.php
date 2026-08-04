<?php echo e(__('Welcome to :company', ['company' => $companyName ?? 'Jana Prints'])); ?>


<?php echo e(__('Hello :name,', ['name' => $employeeName])); ?>


<?php echo e(__('Your employee account has been created.')); ?>


<?php echo e(__('Login email')); ?>: <?php echo e($loginEmail); ?>


<?php echo e(__('Activate your account')); ?>: <?php echo e($activationUrl); ?>


<?php echo e(__('Activation link expires on :date.', ['date' => $expiresAtFormatted])); ?>


<?php echo e(__('Need help? Contact support at :email.', ['email' => $supportEmail])); ?>

<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\employee-onboarding-text.blade.php ENDPATH**/ ?>