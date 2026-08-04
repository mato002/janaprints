<?php if(! empty($paymentDetails) || ! empty($paymentQrPlaceholder)): ?>
    <table class="jp-doc__payment-area" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 4mm;">
        <tr>
            <td style="width: 68%; vertical-align: top; padding-right: 3mm;">
                <?php if(! empty($paymentDetails)): ?>
                    <div class="jp-doc__box">
                        <p class="jp-doc__box-title"><?php echo e(__('Payment Details')); ?></p>
                        <?php $__currentLoopData = $paymentDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p class="jp-doc__box-line"><strong><?php echo e($line['label']); ?>:</strong> <?php echo e($line['value']); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </td>
            <td style="width: 32%; vertical-align: top; padding-left: 3mm;">
                <?php if(! empty($paymentQrPlaceholder)): ?>
                    <div class="jp-doc__qr-placeholder">
                        <p class="jp-doc__qr-placeholder-text"><?php echo e($paymentQrPlaceholder); ?></p>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\payment-details-area.blade.php ENDPATH**/ ?>