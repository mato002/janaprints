New storefront contact message

From: <?php echo e($contactMessage->name); ?>

Email: <?php echo e($contactMessage->email); ?>

<?php if($contactMessage->phone): ?>Phone: <?php echo e($contactMessage->phone); ?>

<?php endif; ?>
<?php if($contactMessage->company): ?>Company: <?php echo e($contactMessage->company); ?>

<?php endif; ?>
Subject: <?php echo e($contactMessage->subject); ?>


Message:
<?php echo e($contactMessage->message); ?>


View in admin: <?php echo e($adminUrl); ?>

<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\public\contact-message-internal-text.blade.php ENDPATH**/ ?>