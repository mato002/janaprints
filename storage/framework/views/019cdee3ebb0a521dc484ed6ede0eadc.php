<table class="jp-doc__header" cellpadding="0" cellspacing="0" data-pdf-branding-header>
    <tr class="jp-doc__header-top">
        <td class="jp-doc__logo-cell">
            <?php if(! empty($document['logoDataUri'])): ?>
                <img src="<?php echo e($document['logoDataUri']); ?>" alt="<?php echo e($document['company']['name'] ?? 'Jana Prints'); ?>" class="jp-doc__logo">
            <?php else: ?>
                <p class="jp-doc__party-name"><?php echo e($document['company']['name'] ?? 'Jana Prints'); ?></p>
            <?php endif; ?>
        </td>
        <td class="jp-doc__title-cell">
            <h1 class="jp-doc__title"><?php echo e($document['title']); ?></h1>
            <p class="jp-doc__number"><?php echo e(__('No.')); ?> <?php echo e($document['documentNumber']); ?></p>
            <?php if(! empty($document['headerHighlight']['value'])): ?>
                <p class="jp-doc__header-highlight-label"><?php echo e($document['headerHighlight']['label']); ?></p>
                <p class="jp-doc__header-highlight-value"><?php echo e($document['headerHighlight']['value']); ?></p>
            <?php endif; ?>
            <?php if(! empty($document['status']['label'])): ?>
                <div class="jp-doc__header-status">
                    <?php echo $__env->make('documents.partials.status-badge', ['status' => $document['status']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>
        </td>
    </tr>
    <tr class="jp-doc__header-address">
        <td colspan="2">
            <div class="jp-doc__company-lines">
                <?php if(! empty($document['company']['name'])): ?>
                    <p class="jp-doc__company-line jp-doc__company-line--name"><?php echo e($document['company']['name']); ?></p>
                <?php endif; ?>
                <?php if(! empty($document['company']['address'])): ?>
                    <p class="jp-doc__company-line"><?php echo e($document['company']['address']); ?></p>
                <?php endif; ?>
                <?php if(! empty($document['company']['phone'])): ?>
                    <p class="jp-doc__company-line"><?php echo e($document['company']['phone']); ?></p>
                <?php endif; ?>
                <?php if(! empty($document['company']['website'])): ?>
                    <p class="jp-doc__company-line"><?php echo e($document['company']['website']); ?></p>
                <?php endif; ?>
                <?php if(! empty($document['company']['email'])): ?>
                    <p class="jp-doc__company-line"><?php echo e($document['company']['email']); ?></p>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/documents/partials/header.blade.php ENDPATH**/ ?>