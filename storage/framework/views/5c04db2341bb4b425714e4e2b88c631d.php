<div data-pdf-branding-header class="pdf-branding-header">
    <?php if(! empty($pdfLogoDataUri)): ?>
        <div class="pdf-branding-header__logo-wrap">
            <img src="<?php echo e($pdfLogoDataUri); ?>" alt="<?php echo e($pdfCompanyName ?? config('app.name')); ?>" class="pdf-branding-header__logo">
        </div>
    <?php elseif(! empty($pdfCompanyName)): ?>
        <p class="pdf-branding-header__company"><?php echo e($pdfCompanyName); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\exports\partials\pdf-header.blade.php ENDPATH**/ ?>