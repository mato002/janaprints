<?php if(! empty($quoteRequestsTopbar['visible']) && ! empty($quoteRequestsTopbar['route'])): ?>
    <a
        href="<?php echo e($quoteRequestsTopbar['route']); ?>"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="<?php echo e($quoteRequestsTopbar['label']); ?>"
    >
        <span><?php echo e($quoteRequestsTopbar['label']); ?></span>
        <?php if(($quoteRequestsTopbar['count'] ?? 0) > 0): ?>
            <span class="erp-quote-topbar-btn__badge"><?php echo e($quoteRequestsTopbar['count']); ?></span>
        <?php endif; ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/layouts/admin/partials/quote-requests-topbar.blade.php ENDPATH**/ ?>