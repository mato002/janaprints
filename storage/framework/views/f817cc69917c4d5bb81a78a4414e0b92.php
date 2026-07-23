<?php
    use App\Enums\ProductionJobCardStatus;

    $completion = $completion ?? ['eligible' => false, 'blockers' => [], 'blocker_codes' => []];
    $checklist = collect($readinessChecklist ?? []);
    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $eligible = (bool) ($completion['eligible'] ?? false);
    $blockers = $completion['blockers'] ?? [];
    $blockerCodes = $completion['blocker_codes'] ?? [];
    $fgWarehouse = $completion['fg_warehouse'] ?? null;
    $remaining = count($blockers);

    $operations = $checklist->firstWhere('key', 'operations');
    $qc = $checklist->firstWhere('key', 'qc');
    $materials = $checklist->firstWhere('key', 'materials');

    $items = [];

    $items[] = [
        'passed' => ($operations['state'] ?? null) === 'passed',
        'label' => __('Production complete'),
        'action' => null,
        'action_label' => null,
    ];

    $items[] = [
        'passed' => ($qc['state'] ?? null) === 'passed',
        'label' => __('QC approved'),
        'action' => ($qc['state'] ?? null) !== 'passed'
            ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])
            : null,
        'action_label' => __('Open QC'),
    ];

    $items[] = [
        'passed' => ($materials['state'] ?? null) === 'passed',
        'label' => ($materials['state'] ?? null) === 'passed'
            ? __('Material consumption recorded')
            : __('Material consumption missing'),
        'action' => ($materials['state'] ?? null) !== 'passed'
            ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'material-consumption', 'open' => 'record-consumption-modal'])
            : null,
        'action_label' => __('Record consumption'),
    ];

    if (in_array('fg_warehouse', $blockerCodes, true)) {
        $items[] = [
            'passed' => false,
            'label' => __('Finished goods warehouse setup incomplete'),
            'action' => null,
            'action_label' => null,
            'hint' => __('Create a branch for this company, then use Verify defaults on Virtual Locations (Supply Chain → Store Operations).'),
        ];
    } elseif ($fgWarehouse) {
        $items[] = [
            'passed' => true,
            'label' => __('Finished goods warehouse (:code)', ['code' => $fgWarehouse['code']]),
            'action' => null,
            'action_label' => null,
        ];
    }

    if (in_array('stock_role', $blockerCodes, true)) {
        $productItem = $jobCard->inventoryItem;
        $items[] = [
            'passed' => false,
            'label' => __('Product stock role incorrect'),
            'action' => $productItem ? route('admin.inventory.items.edit', $productItem) : route('admin.inventory.items.index'),
            'action_label' => __('Open product'),
        ];
    }

    if ($hasPostedOutput) {
        $items[] = [
            'passed' => true,
            'label' => __('Finished goods posted'),
            'action' => null,
            'action_label' => null,
        ];
    } elseif ($eligible) {
        $items[] = [
            'passed' => true,
            'label' => __('Ready to post finished goods'),
            'action' => null,
            'action_label' => null,
        ];
    }

    $failedCount = collect($items)->where('passed', false)->count();
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'job-360-readiness-panel h-fit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'job-360-readiness-panel h-fit']); ?>
    <div class="mb-3 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">
            <?php if($eligible): ?>
                <?php echo e(__('Ready to post')); ?>

            <?php else: ?>
                <?php echo e(__('Readiness')); ?>

            <?php endif; ?>
        </h3>
        <?php if(! $eligible && $failedCount > 0): ?>
            <span class="text-xs font-medium text-amber-700"><?php echo e(trans_choice(':count requirement remaining|:count requirements remaining', $failedCount, ['count' => $failedCount])); ?></span>
        <?php endif; ?>
    </div>

    <ul class="space-y-2">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start justify-between gap-2 text-sm">
                <div class="flex min-w-0 items-start gap-2">
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'mt-0.5 shrink-0 font-bold',
                        'text-emerald-600' => $item['passed'],
                        'text-red-600' => ! $item['passed'],
                    ]); ?>"><?php echo e($item['passed'] ? '✔' : '✖'); ?></span>
                    <span class="text-slate-800"><?php echo e($item['label']); ?></span>
                    <?php if(! empty($item['hint'] ?? null)): ?>
                        <p class="mt-0.5 text-xs text-slate-500"><?php echo e($item['hint']); ?></p>
                    <?php endif; ?>
                </div>
                <?php if(! $item['passed'] && $item['action']): ?>
                    <a href="<?php echo e($item['action']); ?>" class="shrink-0 text-xs font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main"><?php echo e($item['action_label']); ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <?php if($hasPostedOutput): ?>
        <div class="mt-4 border-t border-erp-border pt-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Next workflow')); ?></p>
            <?php if($jobCard->status === ProductionJobCardStatus::ReadyForDispatch): ?>
                <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch'])); ?>" class="mt-1 inline-flex text-sm font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Proceed to dispatch')); ?></a>
            <?php else: ?>
                <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Dispatch unlocks automatically after finished goods are posted.')); ?></p>
            <?php endif; ?>
        </div>
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

<?php if($jobCard->inventoryItem): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4 h-fit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4 h-fit']); ?>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Inventory summary')); ?></h3>
        <dl class="space-y-1 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('SKU')); ?></dt><dd class="font-mono text-slate-800"><?php echo e($jobCard->inventoryItem->sku); ?></dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Item')); ?></dt><dd class="text-right text-slate-800"><?php echo e($jobCard->inventoryItem->item_name); ?></dd></div>
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

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4 h-fit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4 h-fit']); ?>
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Job summary')); ?></h3>
    <dl class="space-y-1 text-sm">
        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Work center')); ?></dt><dd class="text-slate-800"><?php echo e($header['work_center'] ?? '—'); ?></dd></div>
        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Due')); ?></dt><dd class="text-slate-800"><?php echo e($header['due_date']?->format('Y-m-d') ?? '—'); ?></dd></div>
        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Progress')); ?></dt><dd class="tabular-nums text-slate-800"><?php echo e($header['progress_percent'] ?? 0); ?>%</dd></div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/finished-goods-readiness-panel.blade.php ENDPATH**/ ?>