<?php if($primary): ?>
    <?php if(($primary['type'] ?? '') === 'link'): ?>
        <a
            href="<?php echo e($primary['url']); ?>"
            class="erp-btn-primary so-360__primary"
            <?php if($primary['modal'] ?? false): ?>
                data-erp-modal-open
            <?php else: ?>
                data-turbo-frame="erp-main"
            <?php endif; ?>
        >
            <?php echo e($primary['label']); ?>

        </a>
    <?php else: ?>
        <form method="POST" action="<?php echo e($primary['action']); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="erp-btn-primary so-360__primary"><?php echo e($primary['label']); ?></button>
        </form>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\workspace\partials\primary-action.blade.php ENDPATH**/ ?>