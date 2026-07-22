<table class="w-full text-sm">
    <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th><?php echo e(__('Date')); ?></th><th><?php echo e(__('Reference')); ?></th><th><?php echo e(__('Description')); ?></th><th><?php echo e(__('Debit')); ?></th><th><?php echo e(__('Credit')); ?></th><th><?php echo e(__('Balance')); ?></th></tr></thead>
    <tbody>
        <?php $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t border-erp-border">
                <td class="py-2"><?php echo e($entry->date); ?></td>
                <td class="font-mono"><?php echo e($entry->reference); ?></td>
                <td><?php echo e($entry->description); ?></td>
                <td class="font-mono"><?php echo e($entry->debit > 0 ? number_format($entry->debit, 2) : '—'); ?></td>
                <td class="font-mono"><?php echo e($entry->credit > 0 ? number_format($entry->credit, 2) : '—'); ?></td>
                <td class="font-mono"><?php echo e(number_format($entry->balance, 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\receivables\partials\ledger-table.blade.php ENDPATH**/ ?>