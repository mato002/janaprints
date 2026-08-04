New storefront quote request

Customer: <?php echo e($quoteRequest->name); ?>

Email: <?php echo e($quoteRequest->email); ?>

Phone: <?php echo e($quoteRequest->phone); ?>

<?php if($quoteRequest->company): ?>Company: <?php echo e($quoteRequest->company); ?>

<?php endif; ?>
Service: <?php echo e($quoteRequest->service_needed); ?>

<?php if($quoteRequest->quantity): ?>Quantity: <?php echo e($quoteRequest->quantity); ?>

<?php endif; ?>
<?php if($quoteRequest->deadline): ?>Deadline: <?php echo e($quoteRequest->deadline); ?>

<?php endif; ?>
<?php if($quoteRequest->artwork_path): ?>Artwork: Attached to this email
<?php endif; ?>

Message:
<?php echo e($quoteRequest->message); ?>


View in admin: <?php echo e($adminUrl); ?>

<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\public\quote-request-internal-text.blade.php ENDPATH**/ ?>