<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => $jobCard->job_card_number,'heading' => $jobCard->job_card_number,'subtitle' => __('Production tracking')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobCard->job_card_number),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobCard->job_card_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production tracking'))]); ?>
    <div class="client-detail">
        <?php if($tracking): ?>
            <div class="client-detail__meta">
                <p><strong><?php echo e(__('Status')); ?>:</strong> <?php echo e($tracking['status_label']); ?></p>
                <p><strong><?php echo e(__('Expected completion')); ?>:</strong> <?php echo e($tracking['expected_completion']?->format('F j, Y') ?: '—'); ?></p>
                <?php if($jobCard->salesOrder): ?>
                    <p><strong><?php echo e(__('Order')); ?>:</strong> <a href="<?php echo e(route('client.orders.show', $jobCard->salesOrder)); ?>" class="client-link"><?php echo e($jobCard->salesOrder->order_number); ?></a></p>
                <?php endif; ?>
            </div>

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
        <?php endif; ?>

        <?php if($deliveryNotes->isNotEmpty()): ?>
            <section class="client-panel mb-6">
                <h3 class="client-panel__title mb-3"><?php echo e(__('Delivery status')); ?></h3>
                <?php $__currentLoopData = $deliveryNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="client-list-item">
                        <span class="client-list-item__primary"><?php echo e($note->delivery_note_number); ?></span>
                        <span class="client-list-item__meta"><?php echo e($note->status->label()); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php endif; ?>

        <?php if($jobCard->salesOrder?->invoices?->isNotEmpty()): ?>
            <section class="client-panel">
                <h3 class="client-panel__title mb-3"><?php echo e(__('Related documents')); ?></h3>
                <?php $__currentLoopData = $jobCard->salesOrder->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('client.invoices.pdf', $invoice)); ?>" class="client-btn client-btn--secondary mb-2 inline-flex" target="_blank" rel="noopener">
                        <?php echo e(__('Invoice PDF')); ?> — <?php echo e($invoice->invoice_number); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\jobs\show.blade.php ENDPATH**/ ?>