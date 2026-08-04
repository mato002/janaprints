<?php if(! empty($paymentDetails)): ?>
    <div class="jp-doc__box">
        <p class="jp-doc__box-title"><?php echo e(__('Payment Details')); ?></p>
        <?php $__currentLoopData = $paymentDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p class="jp-doc__box-line"><strong><?php echo e($line['label']); ?>:</strong> <?php echo e($line['value']); ?></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\payment-details.blade.php ENDPATH**/ ?>