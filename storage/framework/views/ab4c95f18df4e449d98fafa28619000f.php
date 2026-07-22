<?php
    $hasSpec = $tabData['has_specification'] ?? false;
    $sections = $tabData['sections'] ?? [];
    $sectionLabels = [
        'general' => __('General'),
        'material' => __('Material'),
        'printing' => __('Printing'),
        'finishing' => __('Finishing'),
        'production' => __('Production'),
        'artwork' => __('Artwork'),
        'delivery' => __('Delivery'),
        'notes' => __('Production notes'),
    ];
    $operators = $tabData['operators'] ?? [];
    $recommendations = $tabData['recommendations'] ?? [];
    $materialPlan = $tabData['material_plan'] ?? [];
    $costSummary = $tabData['cost_summary'] ?? null;
    $qcHints = $tabData['qc_hints'] ?? [];
    $pipeline = $tabData['timeline_pipeline'] ?? [];
    $artwork = $tabData['artwork'] ?? [];
?>

<div class="manufacturing-tab space-y-4">
    <div class="sticky top-0 z-10 -mx-1 flex flex-wrap items-center gap-2 border-b border-erp-border bg-white/95 px-1 py-2 backdrop-blur sm:static sm:border-0 sm:bg-transparent sm:p-0">
        <?php if($hasSpec && ! empty($tabData['edit_url'])): ?>
            <a href="<?php echo e($tabData['edit_url']); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Edit specification')); ?></a>
        <?php endif; ?>
        <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Materials')); ?></a>
        <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('QC')); ?></a>
    </div>

    <?php if(! $hasSpec): ?>
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
            <p class="text-sm text-slate-600"><?php echo e($tabData['empty_message'] ?? __('No structured Production Specification available.')); ?></p>
            <?php if(! empty($tabData['legacy'])): ?>
                <dl class="mt-4 space-y-2 border-t border-erp-border pt-4 text-sm">
                    <?php $__currentLoopData = $tabData['legacy']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($value): ?>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500"><?php echo e(ucfirst(str_replace('_', ' ', $label))); ?></dt>
                                <dd class="font-medium"><?php echo e($value); ?></dd>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <?php else: ?>
        <?php if(! empty($tabData['template_name'])): ?>
            <p class="text-xs text-slate-500"><?php echo e(__('Template')); ?>: <?php echo e($tabData['template_name']); ?></p>
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
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Manufacturing timeline')); ?></h3>
            <ol class="flex flex-wrap gap-2">
                <?php $__currentLoopData = $pipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $tone = match ($stage['state']) {
                            'complete' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'current' => 'bg-erp-primary/10 text-erp-primary border-erp-primary/30 ring-2 ring-erp-primary/20',
                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                        };
                    ?>
                    <li class="rounded-full border px-3 py-1 text-xs font-medium <?php echo e($tone); ?>"><?php echo e($stage['label']); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
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

        <?php $__currentLoopData = $sectionLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(! empty($sections[$key])): ?>
                <details class="rounded-lg border border-erp-border bg-white" <?php if($loop->first): ?> open <?php endif; ?>>
                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e($label); ?></summary>
                    <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                        <?php $__currentLoopData = $sections[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 shrink-0"><?php echo e($field['label']); ?></dt>
                                <dd class="text-right font-medium"><?php echo e($field['value'] ?? '—'); ?></dd>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </dl>
                </details>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(! empty($materialPlan['paper']) || ! empty($materialPlan['estimated_sheets'])): ?>
            <details class="rounded-lg border border-erp-border bg-white" open>
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Material summary')); ?></summary>
                <div class="border-t border-erp-border px-4 py-3 text-sm">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <div class="text-xs text-slate-500"><?php echo e(__('Paper')); ?></div>
                            <div class="font-medium"><?php echo e($materialPlan['paper'] ?? '—'); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500"><?php echo e(__('Estimated sheets')); ?></div>
                            <div class="font-medium tabular-nums"><?php echo e($materialPlan['estimated_sheets'] ?? '—'); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500"><?php echo e(__('Waste')); ?></div>
                            <div class="font-medium tabular-nums">
                                <?php if($materialPlan['waste_percent'] !== null): ?>
                                    <?php echo e(number_format((float) $materialPlan['waste_percent'], 1)); ?>%
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Planning view only — stock is not reserved from this panel.')); ?></p>
                </div>
            </details>
        <?php endif; ?>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Machine recommendation')); ?></summary>
            <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Recommended work centre')); ?></dt><dd><?php echo e($recommendations['work_center'] ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Recommended machine')); ?></dt><dd><?php echo e($recommendations['machine'] ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Recommended department')); ?></dt><dd><?php echo e($recommendations['department'] ?? '—'); ?></dd></div>
            </dl>
            <p class="border-t border-erp-border px-4 py-2 text-xs text-slate-500"><?php echo e(__('Recommendations only — operators may override assignments.')); ?></p>
        </details>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Operator information')); ?></summary>
            <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Assigned operator')); ?></dt><dd><?php echo e($operators['operator'] ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Assigned supervisor')); ?></dt><dd><?php echo e($operators['supervisor'] ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Assigned machine')); ?></dt><dd><?php echo e($operators['machine'] ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500"><?php echo e(__('Assigned department')); ?></dt><dd><?php echo e($operators['department'] ?? '—'); ?></dd></div>
            </dl>
        </details>

        <?php if(! empty($artwork) && empty($artwork['empty'])): ?>
            <details class="rounded-lg border border-erp-border bg-white">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Artwork')); ?></summary>
                <div class="border-t border-erp-border px-4 py-3 text-sm">
                    <?php if($artwork['request'] ?? null): ?>
                        <p class="font-medium"><?php echo e($artwork['request']->request_number); ?> · v<?php echo e($artwork['request']->current_version); ?></p>
                        <p class="text-xs text-slate-500"><?php echo e(str_replace('_', ' ', $artwork['approval_status'] ?? '')); ?></p>
                        <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork'])); ?>" class="mt-2 inline-block text-xs text-erp-primary"><?php echo e(__('Open artwork tab')); ?></a>
                    <?php else: ?>
                        <p class="text-slate-500"><?php echo e(__('No artwork linked.')); ?></p>
                    <?php endif; ?>
                </div>
            </details>
        <?php endif; ?>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('QC requirements')); ?></summary>
            <ul class="list-inside list-disc space-y-1 border-t border-erp-border px-4 py-3 text-sm text-slate-700">
                <?php $__currentLoopData = $qcHints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($hint); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="border-t border-erp-border px-4 py-2">
                <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])); ?>" class="text-xs text-erp-primary"><?php echo e(__('Open QC tab')); ?></a>
            </div>
        </details>

        <?php if($costSummary): ?>
            <details class="rounded-lg border border-erp-border bg-white">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Cost summary')); ?></summary>
                <div class="grid grid-cols-2 gap-3 border-t border-erp-border px-4 py-3 text-sm sm:grid-cols-4">
                    <div><div class="text-xs text-slate-500"><?php echo e(__('Material')); ?></div><div class="font-semibold tabular-nums"><?php echo e(number_format($costSummary['material'], 2)); ?></div></div>
                    <div><div class="text-xs text-slate-500"><?php echo e(__('Labour')); ?></div><div class="font-semibold tabular-nums"><?php echo e(number_format($costSummary['labor'], 2)); ?></div></div>
                    <div><div class="text-xs text-slate-500"><?php echo e(__('Outsource')); ?></div><div class="font-semibold tabular-nums"><?php echo e(number_format($costSummary['outsource'], 2)); ?></div></div>
                    <div><div class="text-xs text-slate-500"><?php echo e(__('Total')); ?></div><div class="font-semibold tabular-nums"><?php echo e(number_format($costSummary['total'], 2)); ?></div></div>
                </div>
                <p class="border-t border-erp-border px-4 py-2 text-xs text-slate-500"><?php echo e(__('Read-only — use Commercial tab or costing workspace for full detail.')); ?></p>
            </details>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\manufacturing.blade.php ENDPATH**/ ?>