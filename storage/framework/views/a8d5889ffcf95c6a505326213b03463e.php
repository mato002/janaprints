<?php
    $specEntry = $itemSpecifications[$item->id] ?? null;
    $specModel = $specEntry['model'] ?? null;
    $specSummary = $specEntry['summary'] ?? null;
?>
<div class="border-b border-erp-border py-3 last:border-b-0">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="text-sm">
            <p class="font-medium"><?php echo e($item->item_name); ?> × <?php echo e($item->quantity); ?></p>
            <?php if($item->description): ?>
                <p class="text-slate-500"><?php echo e($item->description); ?></p>
            <?php endif; ?>
        </div>
        <div class="text-sm font-mono"><?php echo e(number_format($item->line_total, 2)); ?></div>
    </div>

    <?php if($specSummary && ($specSummary['has_specification'] ?? false)): ?>
        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600 sm:grid-cols-4">
            <?php if($specSummary['production_type_label'] ?? null): ?>
                <div><dt class="text-slate-400"><?php echo e(__('Type')); ?></dt><dd><?php echo e($specSummary['production_type_label']); ?></dd></div>
            <?php endif; ?>
            <?php if($specSummary['size'] ?? null): ?>
                <div><dt class="text-slate-400"><?php echo e(__('Size')); ?></dt><dd><?php echo e($specSummary['size']); ?></dd></div>
            <?php endif; ?>
            <?php if($specSummary['paper'] ?? null): ?>
                <div><dt class="text-slate-400"><?php echo e(__('Paper')); ?></dt><dd><?php echo e($specSummary['paper']); ?></dd></div>
            <?php endif; ?>
            <?php if($specSummary['ups'] ?? null): ?>
                <div><dt class="text-slate-400"><?php echo e(__('Ups')); ?></dt><dd><?php echo e($specSummary['ups']); ?></dd></div>
            <?php endif; ?>
        </dl>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $specModel)): ?>
            <a href="<?php echo e(route('admin.sales-orders.items.specification.edit', [$salesOrder, $item, $specModel])); ?>" class="mt-2 inline-block text-xs text-erp-accent hover:underline">
                <?php echo e(__('Edit specification')); ?>

            </a>
        <?php endif; ?>
    <?php else: ?>
        <p class="mt-2 text-xs text-slate-500"><?php echo e(__('No production specification yet.')); ?></p>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', [App\Models\Production\ProductionSpecification::class, $salesOrder])): ?>
            <a href="<?php echo e(route('admin.sales-orders.items.specification.create', [$salesOrder, $item])); ?>" class="mt-1 inline-block text-xs text-erp-accent hover:underline">
                <?php echo e(__('Add specification')); ?>

            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/partials/item-specification.blade.php ENDPATH**/ ?>