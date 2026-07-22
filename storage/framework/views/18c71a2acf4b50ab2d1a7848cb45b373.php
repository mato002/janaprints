<?php if(! empty($allocations)): ?>
    <?php if(! empty($allocations['rows'])): ?>
        <table class="jp-doc__items" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <?php $__currentLoopData = $allocations['columns'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th style="text-align: <?php echo e($column['align'] ?? 'left'); ?>; <?php if(! empty($column['width'])): ?> width: <?php echo e($column['width']); ?>; <?php endif; ?>">
                            <?php echo e($column['label']); ?>

                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $allocations['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <?php $__currentLoopData = $allocations['columns'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $alignClass = ($column['align'] ?? 'left') === 'right' ? 'is-right' : '';
                            ?>
                            <td class="<?php echo e($alignClass); ?>"><?php echo e($row[$column['key']] ?? ''); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="jp-doc__party-line" style="font-style: italic; margin-top: 2mm;">
            <?php echo e($allocations['emptyMessage'] ?? __('Payment has not been allocated to a specific invoice.')); ?>

        </p>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\allocations-table.blade.php ENDPATH**/ ?>