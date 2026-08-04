<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => $quotation->quotation_number,'heading' => $quotation->quotation_number,'subtitle' => __('Quote details')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->quotation_number),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->quotation_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quote details'))]); ?>
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong><?php echo e(__('Date')); ?>:</strong> <?php echo e($quotation->quotation_date?->format('F j, Y')); ?></p>
            <p><strong><?php echo e(__('Valid until')); ?>:</strong> <?php echo e($quotation->valid_until?->format('F j, Y') ?: '—'); ?></p>
            <p><strong><?php echo e(__('Status')); ?>:</strong> <?php echo $__env->make('client.partials.status-badge', ['status' => $quotation->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
            <p><strong><?php echo e(__('Total')); ?>:</strong> KES <?php echo e(number_format((float) $quotation->total_amount, 0)); ?></p>
        </div>

        <div class="client-actions">
            <?php if (isset($component)) { $__componentOriginal3c4886a9ff00288f144ef8192d533805 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4886a9ff00288f144ef8192d533805 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.documents.pdf-download-button','data' => ['url' => route('client.quotations.pdf', $quotation),'filename' => $quotation->quotation_number,'class' => 'client-btn client-btn--secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('documents.pdf-download-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('client.quotations.pdf', $quotation)),'filename' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->quotation_number),'class' => 'client-btn client-btn--secondary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $attributes = $__attributesOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__attributesOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $component = $__componentOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__componentOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
        </div>

        <?php if($quotation->items->isNotEmpty()): ?>
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Description')); ?></th>
                            <th><?php echo e(__('Qty')); ?></th>
                            <th><?php echo e(__('Unit price')); ?></th>
                            <th><?php echo e(__('Line total')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->description); ?></td>
                                <td><?php echo e(number_format((float) $item->quantity, 0)); ?></td>
                                <td>KES <?php echo e(number_format((float) $item->unit_price, 0)); ?></td>
                                <td>KES <?php echo e(number_format((float) $item->line_total, 0)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if($canRespond): ?>
            <div class="client-review-box">
                <h3 class="client-panel__title"><?php echo e(__('Your decision')); ?></h3>
                <form method="POST" action="<?php echo e(route('client.quotations.accept', $quotation)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="client-btn"><?php echo e(__('Accept quote')); ?></button>
                </form>
                <form method="POST" action="<?php echo e(route('client.quotations.reject', $quotation)); ?>" class="client-review-form">
                    <?php echo csrf_field(); ?>
                    <label for="reason" class="client-label"><?php echo e(__('Decline reason (optional)')); ?></label>
                    <textarea id="reason" name="reason" rows="3" class="client-input"><?php echo e(old('reason')); ?></textarea>
                    <button type="submit" class="client-btn client-btn--danger"><?php echo e(__('Decline quote')); ?></button>
                </form>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\quotations\show.blade.php ENDPATH**/ ?>