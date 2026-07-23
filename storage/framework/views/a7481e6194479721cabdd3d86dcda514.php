<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Receipt :number', ['number' => $sale->sale_number])] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mx-auto max-w-md print:shadow-none','id' => 'pos-receipt']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto max-w-md print:shadow-none','id' => 'pos-receipt']); ?>
        <div class="text-center border-b border-erp-border pb-4 mb-4">
            <h2 class="text-lg font-semibold"><?php echo e($sale->branch?->name ?? config('app.name')); ?></h2>
            <p class="text-sm text-slate-500"><?php echo e(__('POS Receipt')); ?></p>
            <p class="font-mono text-xs mt-1"><?php echo e(__('Receipt')); ?>: <?php echo e($sale->sale_number); ?></p>
            <p class="text-xs text-slate-400"><?php echo e($sale->sale_date->format('Y-m-d H:i')); ?></p>
            <p class="text-xs text-slate-400"><?php echo e(__('Cashier')); ?>: <?php echo e($sale->cashier?->name ?? '—'); ?></p>
        </div>
        <p class="text-sm mb-4"><?php echo e(__('Customer')); ?>: <?php echo e($sale->is_walk_in ? __('Walk-in') : ($sale->customer?->company_name ?? '—')); ?></p>
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="text-xs text-slate-500">
                    <th class="py-1 text-left"><?php echo e(__('Item')); ?></th>
                    <th class="py-1 text-center"><?php echo e(__('Qty')); ?></th>
                    <th class="py-1 text-right"><?php echo e(__('Total')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-1"><?php echo e($item->description); ?></td>
                        <td class="py-1 text-center tabular-nums"><?php echo e(number_format($item->quantity, 3)); ?></td>
                        <td class="py-1 text-right tabular-nums"><?php echo e(number_format($item->line_total, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="border-t border-erp-border pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span><?php echo e(__('Subtotal')); ?></span><span><?php echo e(number_format($sale->subtotal, 2)); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Discount')); ?></span><span><?php echo e(number_format($sale->discount_amount, 2)); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Tax')); ?></span><span><?php echo e(number_format($sale->tax_amount, 2)); ?></span></div>
            <div class="flex justify-between font-bold text-base"><span><?php echo e(__('Total')); ?></span><span><?php echo e(number_format($sale->total_amount, 2)); ?></span></div>
            <div class="flex justify-between"><span><?php echo e(__('Paid')); ?></span><span><?php echo e(number_format($sale->amount_paid, 2)); ?></span></div>
        </div>
        <?php if($sale->payments->isNotEmpty()): ?>
            <div class="border-t border-erp-border pt-2 mt-2 text-sm">
                <p class="font-medium"><?php echo e(__('Payment method')); ?></p>
                <?php $__currentLoopData = $sale->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p class="text-xs text-slate-500 mt-1"><?php echo e(ucfirst(str_replace('_', ' ', $payment->payment_method->value))); ?> — <?php echo e(number_format($payment->amount, 2)); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <p class="mt-6 text-center text-xs text-slate-400"><?php echo e(__('Thank you for your business.')); ?></p>
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
    <div class="mt-4 text-center print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary"><?php echo e(__('Print receipt')); ?></button>
        <button type="button" onclick="window.print()" class="erp-btn-secondary ml-2"><?php echo e(__('Reprint receipt')); ?></button>
        <a href="<?php echo e(route('admin.commercial.pos.counter-sales')); ?>" class="erp-btn-secondary ml-2"><?php echo e(__('New sale')); ?></a>
        <a href="<?php echo e(route('admin.commercial.pos.dashboard')); ?>" class="erp-btn-secondary ml-2"><?php echo e(__('Back to POS')); ?></a>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\receipt.blade.php ENDPATH**/ ?>