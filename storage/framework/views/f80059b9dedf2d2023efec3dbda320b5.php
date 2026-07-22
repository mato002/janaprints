<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Production jobs'),'heading' => __('Production jobs')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production jobs')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production jobs'))]); ?>
    <div class="client-grid client-grid--single">
        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="client-card">
                <div class="client-card__head">
                    <div>
                        <p class="client-card__eyebrow"><?php echo e(__('Job')); ?></p>
                        <h2 class="client-card__title"><?php echo e($jobCard->job_card_number); ?></h2>
                    </div>
                    <?php if(! empty($jobCard->tracking_summary)): ?>
                        <?php echo $__env->make('client.partials.status-badge', ['label' => $jobCard->tracking_summary['status_label']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
                <dl class="client-card__meta">
                    <?php if($jobCard->salesOrder): ?>
                        <div><dt><?php echo e(__('Order')); ?></dt><dd><?php echo e($jobCard->salesOrder->order_number); ?></dd></div>
                    <?php endif; ?>
                    <div><dt><?php echo e(__('Due date')); ?></dt><dd><?php echo e($jobCard->planned_end_date?->format('M j, Y') ?: '—'); ?></dd></div>
                </dl>
                <a href="<?php echo e(route('client.jobs.show', $jobCard)); ?>" class="client-btn client-btn--secondary"><?php echo e(__('Track job')); ?></a>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php echo $__env->make('client.partials.empty-state', [
                'icon' => 'clipboard',
                'message' => __('No production jobs yet. Jobs appear here once your orders enter production.'),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </div>
    <?php echo e($jobs->links()); ?>

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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\jobs\index.blade.php ENDPATH**/ ?>