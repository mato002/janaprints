<?php
    $metrics = $workspace['usage_metrics'];
    $production = $tabData['production'] ?? [];
    $serial = $workspace['serial_summary'];
?>

<div class="grid gap-4 lg:grid-cols-2">
    <section>
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Usage intelligence')); ?></h3>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500"><?php echo e(__('Orders')); ?></dt><dd class="font-medium tabular-nums"><?php echo e($metrics['orders_count']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Revenue')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format((float) $metrics['total_revenue'], 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Last ordered')); ?></dt><dd><?php echo e($metrics['last_ordered_at'] ? \Illuminate\Support\Carbon::parse($metrics['last_ordered_at'])->format('Y-m-d') : '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Last produced')); ?></dt><dd><?php echo e($metrics['last_produced_at'] ? \Illuminate\Support\Carbon::parse($metrics['last_produced_at'])->format('Y-m-d') : '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Average quantity')); ?></dt><dd class="tabular-nums"><?php echo e($metrics['average_quantity'] ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Last selling price')); ?></dt><dd class="tabular-nums"><?php echo e($metrics['last_selling_price'] !== null ? number_format((float) $metrics['last_selling_price'], 2) : '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Current artwork')); ?></dt><dd><?php echo e($workspace['header']['artwork_version'] ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Serial position')); ?></dt><dd><code class="text-xs"><?php echo e(($serial['uses_serial_numbers'] ?? false) ? (($serial['resolved_prefix'] ?? '').($serial['next_number'] ?? '')) : '—'); ?></code></dd></div>
        </dl>
    </section>

    <section>
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Production intelligence')); ?></h3>
        <?php if($production === []): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('Link a product to view production defaults.')); ?></p>
        <?php else: ?>
            <dl class="grid grid-cols-1 gap-2 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Production route')); ?></dt><dd><?php echo e($production['route_label'] ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('BOM version')); ?></dt><dd><?php echo e($production['bom_name'] ? $production['bom_name'].' v'.($production['bom_version'] ?? '—') : '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('QC checklist')); ?></dt><dd><?php echo e($production['qc_checklist'] ?? '—'); ?> <?php if(($production['qc_line_count'] ?? 0) > 0): ?>(<?php echo e($production['qc_line_count']); ?>)<?php endif; ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Estimated duration')); ?></dt><dd><?php echo e($production['estimated_duration_minutes'] ? $production['estimated_duration_minutes'].' '.__('min') : '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Estimated material cost')); ?></dt><dd class="tabular-nums"><?php echo e(number_format((float) ($production['estimated_material_cost'] ?? 0), 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Estimated selling price')); ?></dt><dd class="tabular-nums"><?php echo e(number_format((float) ($production['estimated_selling_price'] ?? 0), 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Serial rule')); ?></dt><dd><?php echo e($production['serial_rule'] ?? '—'); ?></dd></div>
            </dl>
        <?php endif; ?>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\overview.blade.php ENDPATH**/ ?>