<?php if(! empty($meta)): ?>
    <table class="jp-doc__meta-table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 4mm;">
        <?php $__currentLoopData = $meta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="jp-doc__meta-label"><?php echo e($row['label']); ?></td>
                <td><?php echo e($row['value']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\meta.blade.php ENDPATH**/ ?>