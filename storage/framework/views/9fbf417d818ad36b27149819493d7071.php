<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Branch Performance')); ?></h2></div>
    <div class="exec-table-scroll">
        <table class="exec-table">
            <thead>
                <tr>
                    <th><?php echo e(__('Branch')); ?></th>
                    <th class="text-right"><?php echo e(__('Sales')); ?></th>
                    <th class="text-right"><?php echo e(__('Jobs')); ?></th>
                    <th class="text-right"><?php echo e(__('Receivables')); ?></th>
                    <th class="text-right"><?php echo e(__('Profit')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $dashboard['branches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['exec-table__top' => ! empty($row['top'])]); ?>">
                        <td class="font-medium text-erp-primary">
                            <?php echo e($row['name']); ?>

                            <?php if(! empty($row['top'])): ?>
                                <span class="exec-tag"><?php echo e(__('Top')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right tabular-nums"><?php echo e($row['sales']); ?></td>
                        <td class="text-right tabular-nums"><?php echo e($row['jobs']); ?></td>
                        <td class="text-right tabular-nums text-slate-500"><?php echo e($row['receivables']); ?></td>
                        <td class="text-right tabular-nums text-slate-500"><?php echo e($row['profit']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="py-4 text-center text-xs text-slate-500"><?php echo e(__('No branch data for current scope.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/dashboard/partials/branch-performance.blade.php ENDPATH**/ ?>