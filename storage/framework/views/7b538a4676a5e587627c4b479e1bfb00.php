<table class="jp-doc__totals" cellpadding="0" cellspacing="0">
    <?php $__currentLoopData = $totals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="<?php echo e(! empty($row['highlight']) ? 'is-highlight' : ''); ?> <?php echo e(! empty($row['balanceBar']) ? 'is-balance-bar' : ''); ?>">
            <td class="label"><?php echo e($row['label']); ?></td>
            <td class="value"><?php echo e($row['value']); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\totals.blade.php ENDPATH**/ ?>