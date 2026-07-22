<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => $order->order_number,'heading' => $order->order_number,'subtitle' => __('Order tracking')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->order_number),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->order_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Order tracking'))]); ?>
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong><?php echo e(__('Order date')); ?>:</strong> <?php echo e($order->order_date?->format('F j, Y')); ?></p>
            <p><strong><?php echo e(__('Expected completion')); ?>:</strong> <?php echo e($tracking['expected_completion']?->format('F j, Y') ?: '—'); ?></p>
            <p><strong><?php echo e(__('Status')); ?>:</strong> <?php echo $__env->make('client.partials.status-badge', ['label' => $tracking['status_label']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
            <p><strong><?php echo e(__('Total')); ?>:</strong> KES <?php echo e(number_format((float) $order->total_amount, 0)); ?></p>
            <?php if($order->quotation): ?>
                <p><strong><?php echo e(__('Quote reference')); ?>:</strong> <?php echo e($order->quotation->quotation_number); ?></p>
            <?php endif; ?>
            <?php if($order->jobCard): ?>
                <p><strong><?php echo e(__('Production job')); ?>:</strong> <a href="<?php echo e(route('client.jobs.show', $order->jobCard)); ?>" class="client-link"><?php echo e($order->jobCard->job_card_number); ?></a></p>
            <?php endif; ?>
        </div>

        <?php if(! empty($documents['quotation_pdf']) || ($documents['invoices'] ?? collect())->isNotEmpty() || ($documents['payments'] ?? collect())->isNotEmpty()): ?>
            <section class="client-panel mb-6">
                <h3 class="client-panel__title mb-3"><?php echo e(__('Documents')); ?></h3>
                <div class="flex flex-wrap gap-2">
                    <?php if(! empty($documents['quotation_pdf'])): ?>
                        <a href="<?php echo e($documents['quotation_pdf']); ?>" class="client-btn client-btn--secondary" target="_blank" rel="noopener"><?php echo e(__('Quotation PDF')); ?></a>
                    <?php endif; ?>
                    <?php $__currentLoopData = $documents['invoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoiceDoc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($invoiceDoc['pdf']); ?>" class="client-btn client-btn--secondary" target="_blank" rel="noopener"><?php echo e(__('Invoice')); ?> <?php echo e($invoiceDoc['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $documents['payments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paymentDoc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($paymentDoc['receipt']); ?>" class="client-btn client-btn--secondary" target="_blank" rel="noopener"><?php echo e(__('Receipt')); ?> <?php echo e($paymentDoc['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="client-tracking mb-6">
            <h3 class="client-panel__title mb-3"><?php echo e(__('Progress')); ?></h3>
            <ol class="client-tracking__steps">
                <?php $__currentLoopData = $tracking['stages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'client-tracking__step',
                        'is-complete' => $stage['state'] === 'complete',
                        'is-current' => $stage['state'] === 'current',
                    ]); ?>">
                        <span class="client-tracking__label"><?php echo e($stage['label']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </div>

        <?php if($order->items->isNotEmpty()): ?>
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Description')); ?></th>
                            <th><?php echo e(__('Qty')); ?></th>
                            <th><?php echo e(__('Line total')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->description); ?></td>
                                <td><?php echo e(number_format((float) $item->quantity, 0)); ?></td>
                                <td>KES <?php echo e(number_format((float) $item->line_total, 0)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\orders\show.blade.php ENDPATH**/ ?>