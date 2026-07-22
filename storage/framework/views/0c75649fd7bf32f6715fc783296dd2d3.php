<?php
    $snap = $workspace['snapshot'];
?>

<section class="qr-360__card">
    <h2 class="qr-360__card-title"><?php echo e(__('Customer & Request Snapshot')); ?></h2>

    <div class="qr-360__snapshot-grid">
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Customer Name')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['name']); ?></span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Company')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['company'] ?: '—'); ?></span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Phone')); ?></span>
            <a href="tel:<?php echo e(preg_replace('/\s+/', '', $snap['phone'])); ?>" class="qr-360__field-link"><?php echo e($snap['phone']); ?></a>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Email')); ?></span>
            <a href="mailto:<?php echo e($snap['email']); ?>" class="qr-360__field-link"><?php echo e($snap['email']); ?></a>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Service Requested')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['service']); ?></span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Quantity')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['quantity']); ?></span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Deadline')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['deadline']); ?></span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label"><?php echo e(__('Source')); ?></span>
            <span class="qr-360__field-value"><?php echo e($snap['source']); ?></span>
        </div>
    </div>

    <div class="qr-360__note-card">
        <p class="qr-360__note-card-label"><?php echo e(__('Customer Notes')); ?></p>
        <p class="qr-360__note-card-body whitespace-pre-wrap"><?php echo e($snap['message']); ?></p>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\snapshot.blade.php ENDPATH**/ ?>