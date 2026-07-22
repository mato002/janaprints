<?php if(! empty($summary)): ?>
    <div class="jp-doc__summary">
        <p class="jp-doc__summary-title"><?php echo e($summary['title'] ?? __('Invoice Summary')); ?></p>

        <?php if(! empty($summary['overdueDays'])): ?>
            <p class="jp-doc__summary-overdue">
                <?php echo $__env->make('documents.partials.status-badge', [
                    'status' => [
                        'label' => __(':days days overdue', ['days' => $summary['overdueDays']]),
                        'variant' => 'danger',
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </p>
        <?php endif; ?>

        <table class="jp-doc__summary-table" cellpadding="0" cellspacing="0">
            <?php $__currentLoopData = $summary['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e(! empty($row['emphasis']) ? 'is-emphasis' : ''); ?>">
                    <td class="label"><?php echo e($row['label']); ?></td>
                    <td class="value">
                        <?php if(! empty($row['badge'])): ?>
                            <?php echo $__env->make('documents.partials.status-badge', ['status' => $row['badge']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php else: ?>
                            <?php echo e($row['value']); ?>

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\invoice-summary.blade.php ENDPATH**/ ?>