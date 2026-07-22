<p><?php echo e(__('Dear :customer,', ['customer' => $receipt['customer_name']])); ?></p>

<p><?php echo e(__('Thank you for your payment. Please find your receipt details below.')); ?></p>


<ul>
    <li><strong><?php echo e(__('Receipt number')); ?>:</strong> <?php echo e($receipt['receipt_number']); ?></li>
    <li><strong><?php echo e(__('Date')); ?>:</strong> <?php echo e($receipt['payment_date']); ?></li>
    <li><strong><?php echo e(__('Amount')); ?>:</strong> <?php echo e(number_format($receipt['amount'], 2)); ?> <?php echo e($receipt['currency']); ?></li>
    <li><strong><?php echo e(__('Payment method')); ?>:</strong> <?php echo e($receipt['payment_method']); ?></li>
    <li><strong><?php echo e(__('Balance remaining')); ?>:</strong> <?php echo e(number_format($receipt['balance_remaining'], 2)); ?> <?php echo e($receipt['currency']); ?></li>
</ul>

<?php if(! empty($receipt['invoices_settled'])): ?>
    <p><strong><?php echo e(__('Invoices settled')); ?></strong></p>
    <ul>
        <?php $__currentLoopData = $receipt['invoices_settled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($row['invoice_number']); ?> — <?php echo e(number_format($row['amount_applied'], 2)); ?> (<?php echo e(__('balance')); ?>: <?php echo e(number_format($row['balance_remaining'], 2)); ?>)</li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
<?php endif; ?>

<p><a href="<?php echo e($receipt['public_url']); ?>"><?php echo e(__('View professional receipt online')); ?></a></p>

<p class="muted"><?php echo e(__('This is an automated message from :company.', ['company' => $receipt['company_name']])); ?></p>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\customer-payment-receipt.blade.php ENDPATH**/ ?>