Dear <?php echo e($quoteRequest->name); ?>,

Thank you for reaching out to Jana Prints. We have received your quote request and our commercial team is reviewing your requirements.

YOUR REQUEST SUMMARY
--------------------
Service: <?php echo e($quoteRequest->service_needed); ?>

<?php if($quoteRequest->quantity): ?>Quantity: <?php echo e($quoteRequest->quantity); ?>

<?php endif; ?>
<?php if($quoteRequest->deadline): ?>Deadline: <?php echo e($quoteRequest->deadline); ?>

<?php endif; ?>
<?php if($quoteRequest->company): ?>Company: <?php echo e($quoteRequest->company); ?>

<?php endif; ?>
<?php if($quoteRequest->artwork_path): ?>Artwork: Uploaded — our team will review your files
<?php endif; ?>

WHAT HAPPENS NEXT?
1. Our team reviews your project requirements
2. Artwork is checked if you uploaded files
3. Pricing and production guidance are prepared
4. A Jana Prints representative contacts you directly

NEED TO REACH US SOONER?
Email: <?php echo e($contact['email']); ?>

Phone: <?php echo e($contact['phone']); ?>

WhatsApp: https://wa.me/<?php echo e($whatsapp['number']); ?>


Jana Prints
Commercial Printing • Branding • Packaging
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\public\quote-request-confirmation-text.blade.php ENDPATH**/ ?>