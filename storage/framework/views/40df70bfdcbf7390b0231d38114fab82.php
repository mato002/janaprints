<?php
    $summary = $tabData['summary'] ?? [];
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
    $printSource = $tabData['print_specification_source'] ?? null;
    $machine = $tabData['machine'] ?? [];
?>

<div class="job-360-overview">
    <div class="job-360-overview__zones">
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.operations-zone', [
            'jobCard' => $jobCard,
            'executionState' => $executionState ?? [],
            'assignableMachines' => $assignableMachines ?? collect(),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.commercial-zone', [
            'jobCard' => $jobCard,
            'tabData' => $tabData,
            'dispatchSummary' => $dispatchSummary ?? null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php echo $__env->make('admin.production.job-cards.workspace.partials.history-zone', ['jobCard' => $jobCard], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($printSource || ! empty($manufacturingSummary) || ! empty($machine['machine_name'])): ?>
        <details class="job-360-overview__details">
            <summary><?php echo e(__('Job details & specifications')); ?></summary>
            <div class="job-360-overview__details-body">
                <?php if($printSource): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Print specification')); ?></h3>
                        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Source')); ?></dt>
                                <dd class="font-medium"><?php echo e($printSource['order_source_label'] ?? __('—')); ?></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500"><?php echo e(__('Specification')); ?></dt>
                                <dd class="font-medium"><?php echo e($printSource['specification_label'] ?? __('—')); ?></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Product')); ?></dt>
                                <dd class="font-medium"><?php echo e($printSource['product_name'] ?? __('—')); ?></dd>
                            </div>
                        </dl>
                        <?php if(! empty($printSource['production_notes']) || ! empty($printSource['commercial_notes']) || ! empty($printSource['customer_instructions'])): ?>
                            <dl class="mt-4 grid grid-cols-1 gap-3 border-t border-erp-border pt-4 text-sm lg:grid-cols-3">
                                <?php if(! empty($printSource['production_notes'])): ?>
                                    <div>
                                        <dt class="text-slate-500"><?php echo e(__('Production notes')); ?></dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700"><?php echo e($printSource['production_notes']); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if(! empty($printSource['commercial_notes'])): ?>
                                    <div>
                                        <dt class="text-slate-500"><?php echo e(__('Commercial notes')); ?></dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700"><?php echo e($printSource['commercial_notes']); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if(! empty($printSource['customer_instructions'])): ?>
                                    <div>
                                        <dt class="text-slate-500"><?php echo e(__('Customer instructions')); ?></dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700"><?php echo e($printSource['customer_instructions']); ?></dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        <?php endif; ?>
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
                <?php endif; ?>

                <?php if(! empty($manufacturingSummary)): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Manufacturing instructions')); ?></h3>
                            <a href="<?php echo e($manufacturingSummary['manufacturing_url']); ?>" class="text-xs font-medium text-erp-primary" data-turbo-frame="erp-main"><?php echo e(__('Open manufacturing tab')); ?></a>
                        </div>
                        <?php if($manufacturingSummary['has_specification'] ?? false): ?>
                            <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                <div><dt class="text-slate-500"><?php echo e(__('Product')); ?></dt><dd class="font-medium"><?php echo e($manufacturingSummary['product'] ?? '—'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Quantity')); ?></dt><dd class="font-medium"><?php echo e($manufacturingSummary['quantity'] ?? '—'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Production type')); ?></dt><dd class="font-medium"><?php echo e($manufacturingSummary['production_type'] ?? '—'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Estimated sheets')); ?></dt><dd class="font-medium"><?php echo e($manufacturingSummary['estimated_sheets'] ?? '—'); ?></dd></div>
                            </dl>
                        <?php else: ?>
                            <p class="text-sm text-slate-600"><?php echo e($manufacturingSummary['empty_message'] ?? __('No structured Production Specification available.')); ?></p>
                        <?php endif; ?>
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
                <?php endif; ?>

                <?php if(! empty($machine['machine_name'])): ?>
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
                        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Machine profile')); ?></h3>
                        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-slate-500"><?php echo e(__('Machine')); ?></dt><dd class="font-medium"><?php echo e($machine['machine_name']); ?></dd></div>
                            <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php echo e($machine['machine_status']); ?></dd></div>
                            <div><dt class="text-slate-500"><?php echo e(__('Expected throughput')); ?></dt><dd><?php echo e(number_format($machine['expected_throughput'] ?? 0, 2)); ?> / hr</dd></div>
                            <div><dt class="text-slate-500"><?php echo e(__('Availability')); ?></dt><dd><?php echo e($machine['availability']['label'] ?? '—'); ?></dd></div>
                        </dl>
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
                <?php endif; ?>
            </div>
        </details>
    <?php endif; ?>

    <?php echo $__env->make('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\overview.blade.php ENDPATH**/ ?>