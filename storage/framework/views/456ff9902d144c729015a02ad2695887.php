<table class="jp-doc__parties" cellpadding="0" cellspacing="0">
    <tr>
        <td class="jp-doc__parties-bill">
            <p class="jp-doc__section-title"><?php echo e($customerLabel ?? __('Bill To')); ?></p>
            <?php if(! empty($customer['company'])): ?>
                <p class="jp-doc__party-name"><?php echo e($customer['company']); ?></p>
            <?php endif; ?>
            <?php if (! (! empty($customer['compact']))): ?>
                <?php if(! empty($customer['name'])): ?>
                    <p class="jp-doc__party-line"><?php echo e($customer['name']); ?></p>
                <?php endif; ?>
                <?php if(! empty($customer['code'])): ?>
                    <p class="jp-doc__party-line"><?php echo e(__('Code')); ?>: <?php echo e($customer['code']); ?></p>
                <?php endif; ?>
                <?php if(! empty($customer['phone'])): ?>
                    <p class="jp-doc__party-line"><?php echo e($customer['phone']); ?></p>
                <?php endif; ?>
                <?php if(! empty($customer['email'])): ?>
                    <p class="jp-doc__party-line"><?php echo e($customer['email']); ?></p>
                <?php endif; ?>
                <?php if(! empty($customer['address'])): ?>
                    <p class="jp-doc__party-line"><?php echo e($customer['address']); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(empty($customer['company']) && empty($customer['name']) && empty($customer['phone']) && empty($customer['email']) && empty($customer['address'])): ?>
                <p class="jp-doc__party-line">—</p>
            <?php endif; ?>
        </td>
        <td class="jp-doc__parties-dates">
            <?php if(! empty($dates)): ?>
                <table class="jp-doc__dates-table" cellpadding="0" cellspacing="0">
                    <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="jp-doc__dates-label"><?php echo e($row['label']); ?></td>
                            <td class="jp-doc__dates-value"><?php echo e($row['value']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </table>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/documents/partials/parties.blade.php ENDPATH**/ ?>