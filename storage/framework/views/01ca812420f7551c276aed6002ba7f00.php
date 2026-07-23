<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left"><?php echo e(__('Receipt')); ?></th>
                <th class="px-3 py-2 text-left"><?php echo e(__('Payment')); ?></th>
                <th class="px-3 py-2 text-left"><?php echo e(__('Date')); ?></th>
                <th class="px-3 py-2 text-right"><?php echo e(__('Amount')); ?></th>
                <th class="px-3 py-2 text-left"><?php echo e(__('Actions')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-3 py-2 font-mono"><?php echo e($payment->receipt_number); ?></td>
                    <td class="px-3 py-2"><a href="<?php echo e(route('admin.payments.show', $payment)); ?>" class="font-mono text-erp-accent"><?php echo e($payment->payment_number); ?></a></td>
                    <td class="px-3 py-2"><?php echo e($payment->payment_date->format('M j, Y')); ?></td>
                    <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format($payment->amount, 2)); ?></td>
                    <td class="px-3 py-2">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewReceipt', $payment)): ?>
                            <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>" class="text-erp-accent text-xs"><?php echo e(__('View receipt')); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500"><?php echo e(__('No receipts issued')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if(method_exists($receipts, 'links')): ?>
    <div class="mt-4"><?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $receipts]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($receipts)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?></div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\partials\financial-receipts.blade.php ENDPATH**/ ?>