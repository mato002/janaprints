<?php $fin = $dashboard['finance']; ?>
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Finance Snapshot')); ?></h2></div>
    <dl class="exec-dl">
        <div class="exec-dl__row"><dt><?php echo e(__('Revenue MTD')); ?></dt><dd><?php echo e($fin['revenue_mtd']); ?></dd></div>
        <div class="exec-dl__row"><dt><?php echo e(__('Expenses MTD')); ?></dt><dd class="text-slate-500"><?php echo e($fin['expenses_mtd']); ?></dd></div>
        <div class="exec-dl__row"><dt><?php echo e(__('Profit MTD')); ?></dt><dd class="text-slate-500"><?php echo e($fin['profit_mtd']); ?></dd></div>
        <div class="exec-dl__row"><dt><?php echo e(__('Receivables')); ?></dt><dd class="text-slate-500"><?php echo e($fin['receivables']); ?></dd></div>
        <div class="exec-dl__row"><dt><?php echo e(__('Payables')); ?></dt><dd class="text-slate-500"><?php echo e($fin['payables']); ?></dd></div>
        <div class="exec-dl__row"><dt><?php echo e(__('Cash position')); ?></dt><dd class="text-slate-500"><?php echo e($fin['cash_position']); ?></dd></div>
    </dl>
    <p class="mt-1 text-[10px] text-slate-400"><?php echo e(__('Revenue uses confirmed sales orders until finance module is live.')); ?></p>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/dashboard/partials/finance-snapshot.blade.php ENDPATH**/ ?>