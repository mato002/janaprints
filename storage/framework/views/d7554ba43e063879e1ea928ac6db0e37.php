<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(__('Inventory Variance Report')); ?></title>
    <style>
        body { font-family: Inter, Arial, sans-serif; font-size: 12px; color: #1e293b; padding: 24px; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 20px; }
        .meta dt { color: #64748b; font-size: 11px; }
        .meta dd { margin: 0 0 8px; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0 24px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .card .label { color: #64748b; font-size: 11px; }
        .card .value { font-size: 16px; font-weight: 700; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; }
        .totals { margin-top: 20px; width: 50%; }
        .muted { color: #64748b; font-size: 11px; }
    </style>
</head>
<body>
    <h1><?php echo e(__('Inventory Variance Report')); ?></h1>
    <p class="muted"><?php echo e(__('Generated')); ?>: <?php echo e($generatedAt->format('Y-m-d H:i')); ?></p>

    <dl class="meta grid">
        <div><dt><?php echo e(__('Warehouse')); ?></dt><dd><?php echo e($meta['warehouse']); ?></dd></div>
        <div><dt><?php echo e(__('Count date')); ?></dt><dd><?php echo e($meta['count_date']); ?></dd></div>
        <div><dt><?php echo e(__('Prepared by')); ?></dt><dd><?php echo e($meta['prepared_by'] ?? '—'); ?></dd></div>
        <div><dt><?php echo e(__('Approved by')); ?></dt><dd><?php echo e($meta['approved_by'] ?? '—'); ?></dd></div>
    </dl>

    <h2><?php echo e(__('Variance summary')); ?></h2>
    <div class="grid">
        <div class="card"><div class="label"><?php echo e(__('Expected qty')); ?></div><div class="value"><?php echo e(number_format($summary['expected_qty'], 3)); ?></div></div>
        <div class="card"><div class="label"><?php echo e(__('Counted qty')); ?></div><div class="value"><?php echo e(number_format($summary['counted_qty'], 3)); ?></div></div>
        <div class="card"><div class="label"><?php echo e(__('Variance qty')); ?></div><div class="value"><?php echo e(number_format($summary['variance_qty'], 3)); ?></div></div>
        <div class="card"><div class="label"><?php echo e(__('Variance cost')); ?></div><div class="value"><?php echo e(number_format($summary['variance_cost'], 2)); ?></div></div>
    </div>

    <h2><?php echo e(__('Detailed variances')); ?></h2>
    <table>
        <thead>
            <tr>
                <th><?php echo e(__('Item')); ?></th>
                <th><?php echo e(__('SKU')); ?></th>
                <th><?php echo e(__('Expected')); ?></th>
                <th><?php echo e(__('Counted')); ?></th>
                <th><?php echo e(__('Variance')); ?></th>
                <th><?php echo e(__('Unit cost')); ?></th>
                <th><?php echo e(__('Variance value')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($line->inventoryItem?->item_name); ?></td>
                    <td><?php echo e($line->inventoryItem?->sku); ?></td>
                    <td><?php echo e($line->system_quantity); ?></td>
                    <td><?php echo e($line->counted_quantity); ?></td>
                    <td><?php echo e($line->variance_quantity); ?></td>
                    <td><?php echo e(number_format((float) $line->system_unit_cost, 2)); ?></td>
                    <td><?php echo e(number_format((float) $line->variance_value, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7"><?php echo e(__('No variance lines found.')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2><?php echo e(__('Totals')); ?></h2>
    <table class="totals">
        <tbody>
            <tr><th><?php echo e(__('Positive variance')); ?></th><td><?php echo e(number_format($totals['positive_variance'], 2)); ?></td></tr>
            <tr><th><?php echo e(__('Negative variance')); ?></th><td><?php echo e(number_format($totals['negative_variance'], 2)); ?></td></tr>
            <tr><th><?php echo e(__('Net variance')); ?></th><td><?php echo e(number_format($totals['net_variance'], 2)); ?></td></tr>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\control\variances\exports\pdf.blade.php ENDPATH**/ ?>