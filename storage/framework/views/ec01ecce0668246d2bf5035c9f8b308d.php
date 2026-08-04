<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Statements'),'heading' => __('Account statements')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Statements')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Account statements'))]); ?>
    <div class="client-detail">
        <form method="get" action="<?php echo e(route('client.statements.index')); ?>" class="client-form-grid mb-6">
            <div>
                <label for="from_date" class="client-label"><?php echo e(__('From date')); ?></label>
                <input type="date" id="from_date" name="from_date" value="<?php echo e($fromDate); ?>" class="client-input" required>
            </div>
            <div>
                <label for="to_date" class="client-label"><?php echo e(__('To date')); ?></label>
                <input type="date" id="to_date" name="to_date" value="<?php echo e($toDate); ?>" class="client-input" required>
            </div>
            <div class="client-form-actions">
                <button type="submit" name="preview" value="1" class="client-button client-button--secondary"><?php echo e(__('Preview')); ?></button>
                <a href="<?php echo e(route('client.statements.download', ['from_date' => $fromDate, 'to_date' => $toDate, 'format' => 'pdf'])); ?>" class="client-button" data-turbo="false"><?php echo e(__('Download PDF')); ?></a>
            </div>
        </form>

        <?php if($report): ?>
            <div class="client-detail__meta mb-4">
                <p><strong><?php echo e(__('Opening balance')); ?>:</strong> KES <?php echo e(number_format((float) $report['opening_balance'], 2)); ?></p>
                <p><strong><?php echo e(__('Closing balance')); ?>:</strong> KES <?php echo e(number_format((float) $report['closing_balance'], 2)); ?></p>
            </div>

            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Date')); ?></th>
                            <th><?php echo e(__('Type')); ?></th>
                            <th><?php echo e(__('Reference')); ?></th>
                            <th><?php echo e(__('Description')); ?></th>
                            <th><?php echo e(__('Debit')); ?></th>
                            <th><?php echo e(__('Credit')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $report['entries']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($entry->date); ?></td>
                                <td><?php echo e($entry->type); ?></td>
                                <td><?php echo e($entry->reference); ?></td>
                                <td><?php echo e($entry->description); ?></td>
                                <td><?php echo e($entry->debit > 0 ? number_format($entry->debit, 2) : ''); ?></td>
                                <td><?php echo e($entry->credit > 0 ? number_format($entry->credit, 2) : ''); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="client-empty"><?php echo e(__('No transactions in this period.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php echo $__env->make('client.partials.empty-state', [
                'icon' => 'document',
                'message' => __('Choose a date range and preview your account statement.'),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\statements\index.blade.php ENDPATH**/ ?>