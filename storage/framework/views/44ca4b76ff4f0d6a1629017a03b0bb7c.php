<form method="GET" action="<?php echo e(route('admin.crm.customers.show', $customer)); ?>" class="mb-4 flex flex-wrap items-end gap-3">
    <input type="hidden" name="tab" value="financial">
    <input type="hidden" name="financial_section" value="statement">
    <div>
        <label class="text-xs text-slate-600" for="statement_from"><?php echo e(__('From')); ?></label>
        <input type="date" id="statement_from" name="statement_from" class="erp-input mt-1" value="<?php echo e($from); ?>">
    </div>
    <div>
        <label class="text-xs text-slate-600" for="statement_to"><?php echo e(__('To')); ?></label>
        <input type="date" id="statement_to" name="statement_to" class="erp-input mt-1" value="<?php echo e($to); ?>">
    </div>
    <button type="submit" class="erp-btn-secondary"><?php echo e(__('Generate')); ?></button>
</form>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mb-4 flex flex-wrap justify-between gap-2 text-sm">
        <div>
            <p class="font-semibold"><?php echo e($statement['customer']->company_name); ?></p>
            <p class="text-slate-500"><?php echo e($from); ?> — <?php echo e($to); ?></p>
        </div>
        <div class="text-right">
            <p><?php echo e(__('Opening')); ?>: <span class="font-mono"><?php echo e(number_format($statement['opening_balance'] ?? 0, 2)); ?></span></p>
            <p><?php echo e(__('Closing')); ?>: <span class="font-mono font-semibold"><?php echo e(number_format($statement['closing_balance'] ?? 0, 2)); ?></span></p>
        </div>
    </div>

    <?php echo $__env->make('admin.sales.receivables.partials.ledger-table', ['entries' => $statement['entries']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\partials\financial-statement.blade.php ENDPATH**/ ?>