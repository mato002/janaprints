<div class="grid gap-4 sm:grid-cols-2 text-sm">
    <div><p class="text-slate-500"><?php echo e(__('Commercial notes')); ?></p><p class="whitespace-pre-wrap"><?php echo e($tabData['commercial_notes'] ?: '—'); ?></p></div>
    <div><p class="text-slate-500"><?php echo e(__('Default quantity')); ?></p><p class="tabular-nums"><?php echo e($tabData['default_quantity'] ?? '—'); ?></p></div>
    <div><p class="text-slate-500"><?php echo e(__('Default unit price')); ?></p><p class="tabular-nums"><?php echo e($tabData['default_unit_price'] !== null ? number_format((float) $tabData['default_unit_price'], 2) : '—'); ?></p></div>
    <div><p class="text-slate-500"><?php echo e(__('Default billing type')); ?></p><p><?php echo e($tabData['default_billing_type'] ?? '—'); ?></p></div>
    <div><p class="text-slate-500"><?php echo e(__('Default fulfilment')); ?></p><p><?php echo e($tabData['default_fulfilment_method'] ?? '—'); ?></p></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\commercial_defaults.blade.php ENDPATH**/ ?>