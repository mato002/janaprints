<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Invoices'),'heading' => __('Invoices')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Invoices')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Invoices'))]); ?>
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th><?php echo e(__('Invoice')); ?></th>
                    <th><?php echo e(__('Date')); ?></th>
                    <th><?php echo e(__('Due date')); ?></th>
                    <th><?php echo e(__('Total')); ?></th>
                    <th><?php echo e(__('Balance')); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td data-label="<?php echo e(__('Invoice')); ?>"><?php echo e($invoice->invoice_number); ?></td>
                        <td data-label="<?php echo e(__('Date')); ?>"><?php echo e($invoice->invoice_date?->format('M j, Y')); ?></td>
                        <td data-label="<?php echo e(__('Due date')); ?>"><?php echo e($invoice->due_date?->format('M j, Y') ?: '—'); ?></td>
                        <td data-label="<?php echo e(__('Total')); ?>">KES <?php echo e(number_format((float) $invoice->total_amount, 0)); ?></td>
                        <td data-label="<?php echo e(__('Balance')); ?>">KES <?php echo e(number_format((float) $invoice->balance_due, 0)); ?></td>
                        <td data-label="<?php echo e(__('Action')); ?>"><a href="<?php echo e(route('client.invoices.show', $invoice)); ?>" class="client-link"><?php echo e(__('Open')); ?></a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="client-empty"><?php echo e(__('No invoices yet.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo e($invoices->links()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\invoices\index.blade.php ENDPATH**/ ?>