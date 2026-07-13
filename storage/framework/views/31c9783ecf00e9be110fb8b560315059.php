<?php
    $inboxUnreadSummaryUrl = \Illuminate\Support\Facades\Route::has('admin.communications.inbox.unread-summary')
        ? route('admin.communications.inbox.unread-summary')
        : null;
?>

<?php if(! empty($inboxTopbar['visible']) && ! empty($inboxTopbar['route'])): ?>
    <a
        href="<?php echo e($inboxTopbar['route']); ?>"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="<?php echo e($inboxTopbar['label']); ?>"
        data-inbox-topbar-link
        <?php if($inboxUnreadSummaryUrl): ?> data-inbox-unread-summary-url="<?php echo e($inboxUnreadSummaryUrl); ?>" <?php endif; ?>
    >
        <span><?php echo e($inboxTopbar['label']); ?></span>
        <span
            class="erp-quote-topbar-btn__badge"
            data-inbox-topbar-badge
            <?php if(($inboxTopbar['count'] ?? 0) <= 0): ?> hidden <?php endif; ?>
        ><?php echo e($inboxTopbar['count'] ?? 0); ?></span>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/layouts/admin/partials/inbox-topbar.blade.php ENDPATH**/ ?>