<?php
    $summary = $tabData['summary'] ?? [];
    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;
    $queue = $tabData['queue'] ?? [];
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
    $printSource = $tabData['print_specification_source'] ?? null;
?>

<?php if($printSource): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Source')); ?></h3>
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
                <dt class="text-slate-500"><?php echo e(__('Artwork')); ?></dt>
                <dd class="font-medium">
                    <?php if($printSource['artwork_version'] ?? null): ?>
                        <?php echo e(__('Version :number', ['number' => $printSource['artwork_version']])); ?>

                    <?php else: ?>
                        <?php echo e(__('—')); ?>

                    <?php endif; ?>
                </dd>
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

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1']); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Queue status')); ?></h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Current queue')); ?></dt><dd><?php echo e($queue['status_label'] ?? __('—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Work center')); ?></dt><dd><?php echo e($queue['work_center'] ?? __('—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Queue position')); ?></dt><dd><?php echo e($queue['position'] ?? __('—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt><dd><?php echo e(str_replace('_', ' ', $queue['priority'] ?? '—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Required date')); ?></dt><dd><?php echo e($queue['required_date'] ?? __('—')); ?></dd></div>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1']); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Job summary')); ?></h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Type')); ?></dt><dd><?php echo e(str_replace('_', ' ', $summary['production_type'] ?? '—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt><dd><?php echo e(str_replace('_', ' ', $summary['priority'] ?? '—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Planned')); ?></dt><dd><?php echo e(($summary['planned']['start'] ?? '—')); ?> → <?php echo e(($summary['planned']['end'] ?? '—')); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Actual')); ?></dt><dd><?php echo e(($summary['actual']['start'] ?? '—')); ?> → <?php echo e(($summary['actual']['end'] ?? '—')); ?></dd></div>
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

    <div class="lg:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
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
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Customer')); ?></h3>
            <?php if($customer): ?>
                <p class="text-sm font-medium"><?php echo e($customer['name']); ?></p>
                <p class="text-xs text-slate-500"><?php echo e($customer['code']); ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No customer linked.')); ?></p>
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
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Sales order')); ?></h3>
            <?php if($salesOrder): ?>
                <p class="text-sm font-medium"><?php echo e($salesOrder['number']); ?></p>
                <p class="text-xs text-slate-500"><?php echo e(str_replace('_', ' ', $salesOrder['status'])); ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No sales order linked.')); ?></p>
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
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Quotation')); ?></h3>
            <?php if($quotation): ?>
                <p class="text-sm font-medium"><?php echo e($quotation['number']); ?></p>
                <p class="text-xs text-slate-500"><?php echo e(str_replace('_', ' ', $quotation['status'])); ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No quotation linked.')); ?></p>
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
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Artwork')); ?></h3>
            <?php if($printSource && ($printSource['artwork_version'] ?? null)): ?>
                <p class="text-sm font-medium"><?php echo e(__('Version :number', ['number' => $printSource['artwork_version']])); ?></p>
                <p class="text-xs text-slate-500"><?php echo e(__('From customer print specification')); ?></p>
            <?php elseif($artwork): ?>
                <p class="text-sm font-medium"><?php echo e($artwork['number']); ?></p>
                <p class="text-xs text-slate-500"><?php echo e(str_replace('_', ' ', $artwork['status'])); ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No artwork linked.')); ?></p>
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
    </div>
</div>

<?php if(! empty($manufacturingSummary)): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Manufacturing instructions')); ?></h3>
            <a href="<?php echo e($manufacturingSummary['manufacturing_url']); ?>" class="text-xs font-medium text-erp-primary"><?php echo e(__('Open manufacturing tab')); ?></a>
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

<?php $machine = $tabData['machine'] ?? []; ?>
<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Assigned Machine')); ?></h3>
    <?php if(! empty($machine['machine_name'])): ?>
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500"><?php echo e(__('Machine')); ?></dt><dd class="font-medium"><?php echo e($machine['machine_name']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php echo e($machine['machine_status']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Expected Throughput')); ?></dt><dd><?php echo e(number_format($machine['expected_throughput'] ?? 0, 2)); ?> / hr</dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Availability')); ?></dt><dd><?php echo e($machine['availability']['label'] ?? '—'); ?></dd></div>
        </dl>
        <?php if(($machine['assignment_history'] ?? collect())->isNotEmpty()): ?>
            <div class="mt-4">
                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Assignment History')); ?></h4>
                <ul class="space-y-1 text-sm text-slate-600">
                    <?php $__currentLoopData = $machine['assignment_history']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($history->assigned_at?->format('Y-m-d H:i')); ?> — <?php echo e($history->assigner?->name); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No machine assigned.')); ?></p>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('machines.assign')): ?>
        <?php if(($tabData['machine_options'] ?? collect())->isNotEmpty()): ?>
            <form method="POST" action="<?php echo e(route('admin.production.job-cards.assign-machine', $jobCard)); ?>" class="mt-4 flex flex-wrap items-end gap-2">
                <?php echo csrf_field(); ?>
                <div class="min-w-[14rem] flex-1">
                    <label class="erp-label"><?php echo e(__('Assign Machine')); ?></label>
                    <select name="assigned_machine_asset_id" class="erp-select w-full" required>
                        <option value=""><?php echo e(__('Select machine…')); ?></option>
                        <?php $__currentLoopData = $tabData['machine_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->fixed_asset_id); ?>"><?php echo e($option->asset?->asset_name); ?> (<?php echo e($option->machine_code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Assign')); ?></button>
            </form>
        <?php endif; ?>
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

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Current status')); ?></h3>
    <p class="text-sm text-slate-700"><?php echo e($tabData['status_explanation'] ?? ''); ?></p>
    <p class="mt-3 text-sm"><span class="font-medium text-erp-primary"><?php echo e(__('Next action')); ?>:</span> <?php echo e($tabData['next_action'] ?? ''); ?></p>
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

<?php echo $__env->make('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\overview.blade.php ENDPATH**/ ?>