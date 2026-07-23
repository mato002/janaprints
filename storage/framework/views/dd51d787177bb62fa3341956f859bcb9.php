<?php $production = $tabData['production'] ?? []; $serial = $tabData['serial_summary'] ?? []; ?>

<div class="space-y-4 text-sm">
    <section>
        <h3 class="mb-2 font-semibold text-slate-900"><?php echo e(__('Production notes')); ?></h3>
        <p class="whitespace-pre-wrap text-slate-700"><?php echo e($tabData['production_notes'] ?: '—'); ?></p>
    </section>
    <section>
        <h3 class="mb-2 font-semibold text-slate-900"><?php echo e(__('Customer instructions')); ?></h3>
        <p class="whitespace-pre-wrap text-slate-700"><?php echo e($tabData['customer_instructions'] ?: '—'); ?></p>
    </section>
    <?php if($production !== []): ?>
        <section>
            <h3 class="mb-2 font-semibold text-slate-900"><?php echo e(__('Route & BOM')); ?></h3>
            <dl class="grid gap-2">
                <div><dt class="text-slate-500"><?php echo e(__('Route')); ?></dt><dd><?php echo e($production['route_label'] ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('BOM')); ?></dt><dd><?php echo e($production['bom_name'] ? $production['bom_name'].' v'.($production['bom_version'] ?? '') : '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('QC checklist')); ?></dt><dd><?php echo e($production['qc_checklist'] ?? '—'); ?></dd></div>
            </dl>
        </section>
    <?php endif; ?>
    <?php if($serial['uses_serial_numbers'] ?? false): ?>
        <section>
            <h3 class="mb-2 font-semibold text-slate-900"><?php echo e(__('Serial rule')); ?></h3>
            <p><code><?php echo e($serial['resolved_prefix']); ?><?php echo e($serial['next_number']); ?></code></p>
        </section>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\production_defaults.blade.php ENDPATH**/ ?>