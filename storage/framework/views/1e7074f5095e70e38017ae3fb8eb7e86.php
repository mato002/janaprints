<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tab_data', 'active_tab', 'tabs', 'filters']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['tab_data', 'active_tab', 'tabs', 'filters']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(($tab_data['type'] ?? '') === 'placeholder'): ?>
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
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'document-text','title' => __('Quotation Reports'),'description' => $tab_data['message'] ?? __('No data available.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quotation Reports')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tab_data['message'] ?? __('No data available.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
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
<?php elseif(($tab_data['type'] ?? '') === 'summary'): ?>
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
        <h2 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Quotation Summary')); ?></h2>
        <?php $m = $tab_data['metrics'] ?? []; ?>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Quotes Issued')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($m['issued'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Quotes Accepted')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($m['accepted'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Total Quote Value')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e('KES '.number_format($m['total_value'] ?? 0, 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Conversion %')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(($m['conversion'] ?? 0).'%'); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Open Quotes')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($m['open'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Quotes Rejected')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($m['rejected'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Quotes Expired')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($m['expired'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Avg Approval Time')); ?></p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">
                    <?php echo e(isset($m['avg_approval_hours']) && $m['avg_approval_hours'] !== null ? $m['avg_approval_hours'].' '.__('hrs') : '—'); ?>

                </p>
            </div>
        </div>
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
<?php elseif(($tab_data['type'] ?? '') === 'win_rate'): ?>
    <?php $win = $tab_data['data'] ?? []; ?>
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
        <h2 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Quote Win Rate')); ?></h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Issued')); ?></p>
                <p class="text-2xl font-bold tabular-nums text-erp-primary"><?php echo e(number_format($win['issued'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Won')); ?></p>
                <p class="text-2xl font-bold tabular-nums text-emerald-600"><?php echo e(number_format($win['won'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Win Rate')); ?></p>
                <p class="text-2xl font-bold tabular-nums text-erp-primary"><?php echo e(($win['win_rate'] ?? 0).'%'); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Loss Rate')); ?></p>
                <p class="text-2xl font-bold tabular-nums text-rose-600"><?php echo e(($win['loss_rate'] ?? 0).'%'); ?></p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500"><?php echo e(__('Accepted')); ?></p>
                <p class="text-lg font-semibold tabular-nums"><?php echo e(number_format($win['accepted'] ?? 0)); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500"><?php echo e(__('Converted')); ?></p>
                <p class="text-lg font-semibold tabular-nums"><?php echo e(number_format($win['converted'] ?? 0)); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500"><?php echo e(__('Rejected')); ?></p>
                <p class="text-lg font-semibold tabular-nums"><?php echo e(number_format($win['rejected'] ?? 0)); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500"><?php echo e(__('Expired')); ?></p>
                <p class="text-lg font-semibold tabular-nums"><?php echo e(number_format($win['expired'] ?? 0)); ?></p>
            </div>
        </div>
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
<?php elseif(($tab_data['type'] ?? '') === 'table'): ?>
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
        <?php
            $rows = $tab_data['rows'] ?? [];
            $tableRows = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? collect($rows->items())->map(fn ($row) => array_values((array) $row))->all()
                : collect($rows)->map(fn ($row) => array_values((array) $row))->all();
        ?>
        <?php echo $__env->make('admin.commercial.reports.sales.partials.simple-table', [
            'title' => $tab_data['title'] ?? collect($tabs)->firstWhere('key', $active_tab)['label'] ?? __('Report'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => $tableRows,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator): ?>
            <div class="mt-4 border-t border-erp-border pt-4">
                <?php echo e($rows->withQueryString()->links()); ?>

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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\quotations\partials\tab-content.blade.php ENDPATH**/ ?>