<table class="jp-doc__items" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="text-align: <?php echo e($column['align'] ?? 'left'); ?>; <?php if(! empty($column['width'])): ?> width: <?php echo e($column['width']); ?>; <?php endif; ?>">
                    <?php echo e($column['label']); ?>

                </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $alignClass = ($column['align'] ?? 'left') === 'right' ? 'is-right' : '';
                    ?>
                    <td class="<?php echo e($alignClass); ?>"><?php echo e($item[$column['key']] ?? ''); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="<?php echo e(count($columns)); ?>" class="jp-doc__empty"><?php echo e(__('No line items on this document.')); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\items-table.blade.php ENDPATH**/ ?>