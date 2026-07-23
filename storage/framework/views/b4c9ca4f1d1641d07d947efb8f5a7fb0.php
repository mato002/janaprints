<?php
    $statement = $statement ?? [];
    $lines = $statement['lines'] ?? [];
?>

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
    <?php if (isset($component)) { $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.secondary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('secondary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Generate')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $attributes = $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $component = $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('statements.export')): ?>
        <a href="<?php echo e(route('admin.crm.customers.statement', ['customer' => $customer, 'statement_from' => $from, 'statement_to' => $to, 'export' => 'json'])); ?>" class="erp-btn-secondary"><?php echo e(__('Export JSON')); ?></a>
    <?php endif; ?>
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
            <p class="font-semibold"><?php echo e($statement['customer']['name'] ?? ''); ?></p>
            <p class="text-slate-500"><?php echo e($from); ?> — <?php echo e($to); ?></p>
        </div>
        <div class="text-right">
            <p><?php echo e(__('Opening')); ?>: <span class="font-mono"><?php echo e(number_format($statement['opening_balance'] ?? 0, 2)); ?></span></p>
            <p><?php echo e(__('Closing')); ?>: <span class="font-mono font-semibold"><?php echo e(number_format($statement['closing_balance'] ?? 0, 2)); ?></span></p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-left">
                    <th class="px-3 py-2"><?php echo e(__('Date')); ?></th>
                    <th class="px-3 py-2"><?php echo e(__('Type')); ?></th>
                    <th class="px-3 py-2"><?php echo e(__('Reference')); ?></th>
                    <th class="px-3 py-2"><?php echo e(__('Description')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Debit')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Credit')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Balance')); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-100 font-medium">
                    <td class="px-3 py-2" colspan="6"><?php echo e(__('Opening balance')); ?></td>
                    <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format($statement['opening_balance'] ?? 0, 2)); ?></td>
                </tr>
                <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-slate-50">
                        <td class="px-3 py-2 whitespace-nowrap"><?php echo e($line['date']); ?></td>
                        <td class="px-3 py-2 capitalize"><?php echo e(str_replace('_', ' ', $line['type'])); ?></td>
                        <td class="px-3 py-2 font-mono text-xs"><?php echo e($line['reference'] ?? '—'); ?></td>
                        <td class="px-3 py-2"><?php echo e($line['description']); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e(($line['debit'] ?? 0) > 0 ? number_format($line['debit'], 2) : '—'); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e(($line['credit'] ?? 0) > 0 ? number_format($line['credit'], 2) : '—'); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format($line['balance'] ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500"><?php echo e(__('No transactions in this period.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\partials\statement-ledger.blade.php ENDPATH**/ ?>