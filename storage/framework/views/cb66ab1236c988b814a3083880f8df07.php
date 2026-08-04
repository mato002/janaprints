<?php if($active->customer): ?>
    <a href="<?php echo e(route('admin.crm.customers.show', $active->customer)); ?>" class="erp-btn erp-btn--primary erp-btn--sm w-full" data-turbo-frame="erp-main">
        <?php echo e(__('Customer profile (360)')); ?>

    </a>
    <p class="mt-2 text-[11px] text-slate-500"><?php echo e(__('Phone, email, credit, and full history live on the customer profile — not duplicated here.')); ?></p>
<?php else: ?>
    <p class="text-sm text-slate-500"><?php echo e(__('Link a customer to open their profile and ERP records.')); ?></p>
<?php endif; ?>

<?php if($context && ! empty($context['summary_compact'])): ?>
    <?php $s = $context['summary_compact']; ?>
    <?php if((float) str_replace(',', '', $s['outstanding_balance']) > 0 || (int) $s['open_items_count'] > 0): ?>
        <dl class="mt-4 grid grid-cols-2 gap-2 rounded-lg border border-erp-border bg-white p-3 text-xs">
            <div>
                <dt class="text-slate-500"><?php echo e(__('Outstanding')); ?></dt>
                <dd class="font-semibold text-erp-primary"><?php echo e($s['outstanding_balance']); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Open ERP items')); ?></dt>
                <dd class="font-semibold"><?php echo e($s['open_items_count']); ?></dd>
            </div>
        </dl>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tab-summary.blade.php ENDPATH**/ ?>