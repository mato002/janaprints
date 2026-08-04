<?php if(! empty($alerts)): ?>
    <div class="job-360__alerts mb-4 space-y-2">
        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'rounded-lg border px-4 py-3 text-sm',
                'border-red-200 bg-red-50 text-red-900' => ($alert['type'] ?? '') === 'error',
                'border-amber-200 bg-amber-50 text-amber-900' => ($alert['type'] ?? '') === 'warning',
            ]); ?>">
                <?php echo e($alert['message']); ?>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/control-alerts.blade.php ENDPATH**/ ?>