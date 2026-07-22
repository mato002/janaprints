<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'report',
    /** @var 'standard'|'extended' Standard: debit, credit, balance. Extended: adds debit/credit balance columns. */
    'tableMode' => 'standard',
]));

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

foreach (array_filter(([
    'report',
    /** @var 'standard'|'extended' Standard: debit, credit, balance. Extended: adds debit/credit balance columns. */
    'tableMode' => 'standard',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\GlAccountTypeCode;

    $sectionDefs = [
        'asset' => ['label' => __('Assets'), 'icon' => 'building', 'order' => 1],
        'liability' => ['label' => __('Liabilities'), 'icon' => 'currency-dollar', 'order' => 2],
        'equity' => ['label' => __('Equity'), 'icon' => 'chart-pie', 'order' => 3],
        'revenue' => ['label' => __('Revenue'), 'icon' => 'cash', 'order' => 4],
        'expense' => ['label' => __('Expenses'), 'icon' => 'document-text', 'order' => 5],
        'other' => ['label' => __('Other'), 'icon' => 'inbox', 'order' => 6],
    ];

    $resolveSection = static function (string $accountCode): string {
        $prefix = substr(ltrim($accountCode), 0, 1);

        return match ($prefix) {
            GlAccountTypeCode::Asset->codeRangePrefix() => 'asset',
            GlAccountTypeCode::Liability->codeRangePrefix() => 'liability',
            GlAccountTypeCode::Equity->codeRangePrefix() => 'equity',
            GlAccountTypeCode::Revenue->codeRangePrefix() => 'revenue',
            GlAccountTypeCode::CostOfSales->codeRangePrefix(),
            GlAccountTypeCode::Expense->codeRangePrefix() => 'expense',
            default => 'other',
        };
    };

    $rowNetBalance = static function (array $row): float {
        if (array_key_exists('balance', $row)) {
            return (float) $row['balance'];
        }

        return round((float) ($row['debit_balance'] ?? 0) - (float) ($row['credit_balance'] ?? 0), 2);
    };

    $sections = collect($sectionDefs)->mapWithKeys(fn ($def, $key) => [
        $key => [
            ...$def,
            'key' => $key,
            'rows' => [],
            'count' => 0,
            'period_debit' => 0.0,
            'period_credit' => 0.0,
            'net_balance' => 0.0,
        ],
    ])->all();

    foreach ($report['rows'] as $row) {
        $key = $resolveSection($row['account_code']);
        $net = $rowNetBalance($row);
        $sections[$key]['rows'][] = $row;
        $sections[$key]['count']++;
        $sections[$key]['period_debit'] += (float) $row['period_debit'];
        $sections[$key]['period_credit'] += (float) $row['period_credit'];
        $sections[$key]['net_balance'] += $net;
    }

    foreach ($sections as $key => $section) {
        $sections[$key]['period_debit'] = round($section['period_debit'], 2);
        $sections[$key]['period_credit'] = round($section['period_credit'], 2);
        $sections[$key]['net_balance'] = round($section['net_balance'], 2);
        $sections[$key]['rows'] = collect($section['rows'])->sortBy('account_code')->values()->all();
    }

    $orderedSections = collect($sections)
        ->filter(fn ($s) => $s['count'] > 0 || $s['key'] !== 'other')
        ->sortBy('order')
        ->values();

    $rows = collect($report['rows']);
    $hasRows = $rows->isNotEmpty();

    $detailedRows = $rows->sortBy(function (array $row) use ($resolveSection, $sectionDefs) {
        $key = $resolveSection($row['account_code']);
        $order = $sectionDefs[$key]['order'] ?? 99;

        return sprintf('%02d-%s', $order, $row['account_code']);
    })->values();
?>

<div
    x-data="{
        view: 'summary',
        compact: false,
        open: {
            asset: false,
            liability: false,
            equity: false,
            revenue: false,
            expense: false,
            other: false,
        },
        toggleSection(key) { this.open[key] = !this.open[key]; },
    }"
    class="space-y-4"
>
    
    <div class="sticky top-0 z-20 -mx-1 rounded-xl border border-erp-border bg-white/95 px-3 py-3 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/80">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400"><?php echo e(__('Trial balance')); ?></span>
                <?php if($report['is_balanced']): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'shield-check','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        <?php echo e(__('Balanced')); ?>

                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-amber-600/20">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        <?php echo e(__('Out of balance')); ?>

                    </span>
                <?php endif; ?>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm tabular-nums">
                <div>
                    <span class="text-[11px] uppercase text-slate-400"><?php echo e(__('Total debits')); ?></span>
                    <p class="font-semibold text-erp-primary"><?php echo e(number_format($report['total_debit'], 2)); ?></p>
                </div>
                <div class="hidden h-8 w-px bg-erp-border sm:block" aria-hidden="true"></div>
                <div>
                    <span class="text-[11px] uppercase text-slate-400"><?php echo e(__('Total credits')); ?></span>
                    <p class="font-semibold text-erp-primary"><?php echo e(number_format($report['total_credit'], 2)); ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1 rounded-lg border border-erp-border bg-slate-50/80 p-1" role="tablist">
                <button
                    type="button"
                    role="tab"
                    @click="view = 'summary'"
                    :class="view === 'summary' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                ><?php echo e(__('Summary')); ?></button>
                <button
                    type="button"
                    role="tab"
                    @click="view = 'grouped'"
                    :class="view === 'grouped' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                ><?php echo e(__('Grouped')); ?></button>
                <button
                    type="button"
                    role="tab"
                    @click="view = 'detailed'"
                    :class="view === 'detailed' ? 'bg-white text-erp-primary shadow-sm ring-1 ring-erp-border' : 'text-slate-500 hover:text-erp-primary'"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                ><?php echo e(__('Detailed')); ?></button>
            </div>
        </div>
    </div>

    <?php if(! $hasRows): ?>
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
            <p class="py-8 text-center text-sm text-slate-500"><?php echo e(__('No accounts with activity for the selected filters.')); ?></p>
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
        
        <div x-show="view === 'summary'" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <?php $__currentLoopData = $orderedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($section['count'] === 0): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['hover' => true,'class' => 'border-l-4 border-l-erp-accent/60']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hover' => true,'class' => 'border-l-4 border-l-erp-accent/60']); ?>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400"><?php echo e($section['label']); ?></p>
                                <p class="mt-2 text-lg font-semibold tabular-nums text-erp-primary">
                                    <?php echo e(number_format($section['net_balance'], 2)); ?>

                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    <?php echo e($section['count']); ?> <?php echo e($section['count'] === 1 ? __('account') : __('accounts')); ?>

                                    · <?php echo e(__('Net balance')); ?>

                                </p>
                            </div>
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $section['icon'],'class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section['icon']),'class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-4 border-t border-erp-border/60 pt-3 text-[11px] tabular-nums text-slate-500">
                            <span><?php echo e(__('Dr')); ?> <?php echo e(number_format($section['period_debit'], 2)); ?></span>
                            <span><?php echo e(__('Cr')); ?> <?php echo e(number_format($section['period_credit'], 2)); ?></span>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'bg-slate-50/50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-slate-50/50']); ?>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-erp-primary"><?php echo e(__('Report totals')); ?></p>
                        <p class="text-xs text-slate-500"><?php echo e(__('Period debits and credits across all accounts')); ?></p>
                    </div>
                    <div class="flex flex-wrap gap-6 text-sm tabular-nums">
                        <div><span class="text-slate-400"><?php echo e(__('Debits')); ?></span> <span class="font-semibold"><?php echo e(number_format($report['total_debit'], 2)); ?></span></div>
                        <div><span class="text-slate-400"><?php echo e(__('Credits')); ?></span> <span class="font-semibold"><?php echo e(number_format($report['total_credit'], 2)); ?></span></div>
                        <div>
                            <span class="text-slate-400"><?php echo e(__('Status')); ?></span>
                            <span class="font-semibold <?php echo e($report['is_balanced'] ? 'text-emerald-600' : 'text-amber-600'); ?>">
                                <?php echo e($report['is_balanced'] ? __('Balanced') : __('Out of balance')); ?>

                            </span>
                        </div>
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
        </div>

        
        <div x-show="view === 'grouped'" x-cloak class="space-y-2">
            <?php $__currentLoopData = $orderedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($section['count'] === 0): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <div class="overflow-hidden rounded-xl border border-erp-border bg-white">
                    <button
                        type="button"
                        @click="toggleSection('<?php echo e($section['key']); ?>')"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50/80"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent"
                            >
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $section['icon'],'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section['icon']),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium text-erp-primary"><?php echo e($section['label']); ?></p>
                                <p class="text-xs text-slate-500">
                                    <?php echo e($section['count']); ?> <?php echo e($section['count'] === 1 ? __('account') : __('accounts')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 text-sm tabular-nums">
                            <span class="hidden text-slate-500 sm:inline"><?php echo e(__('Net')); ?> <strong class="text-erp-primary"><?php echo e(number_format($section['net_balance'], 2)); ?></strong></span>
                            <span class="inline-block transition" :class="open['<?php echo e($section['key']); ?>'] && 'rotate-180'">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-down','class' => 'h-5 w-5 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'h-5 w-5 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            </span>
                        </div>
                    </button>

                    <div
                        x-show="open['<?php echo e($section['key']); ?>']"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-cloak
                        class="border-t border-erp-border"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[32rem] text-sm">
                                <thead class="bg-slate-50/90 text-[11px] uppercase text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2 text-left"><?php echo e(__('Account')); ?></th>
                                        <th class="px-4 py-2 text-right"><?php echo e(__('Debit')); ?></th>
                                        <th class="px-4 py-2 text-right"><?php echo e(__('Credit')); ?></th>
                                        <th class="px-4 py-2 text-right"><?php echo e($tableMode === 'extended' ? __('Debit bal.') : __('Balance')); ?></th>
                                        <?php if($tableMode === 'extended'): ?>
                                            <th class="px-4 py-2 text-right"><?php echo e(__('Credit bal.')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $section['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-slate-50/40'); ?> border-b border-erp-border/40 hover:bg-erp-accent/5">
                                            <td class="px-4 py-2">
                                                <span class="font-mono text-xs text-slate-500"><?php echo e($row['account_code']); ?></span>
                                                <span class="text-erp-primary"><?php echo e($row['account_name']); ?></span>
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums"><?php echo e($row['period_debit'] > 0 ? number_format($row['period_debit'], 2) : '—'); ?></td>
                                            <td class="px-4 py-2 text-right tabular-nums"><?php echo e($row['period_credit'] > 0 ? number_format($row['period_credit'], 2) : '—'); ?></td>
                                            <?php if($tableMode === 'extended'): ?>
                                                <td class="px-4 py-2 text-right tabular-nums"><?php echo e(($row['debit_balance'] ?? 0) > 0 ? number_format($row['debit_balance'], 2) : '—'); ?></td>
                                                <td class="px-4 py-2 text-right tabular-nums"><?php echo e(($row['credit_balance'] ?? 0) > 0 ? number_format($row['credit_balance'], 2) : '—'); ?></td>
                                            <?php else: ?>
                                                <td class="px-4 py-2 text-right font-medium tabular-nums"><?php echo e(number_format($rowNetBalance($row), 2)); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot class="bg-slate-100/80 font-semibold text-erp-primary">
                                    <tr>
                                        <td class="px-4 py-2"><?php echo e(__('Section subtotal')); ?></td>
                                        <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format($section['period_debit'], 2)); ?></td>
                                        <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format($section['period_credit'], 2)); ?></td>
                                        <?php if($tableMode === 'extended'): ?>
                                            <td colspan="2" class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format($section['net_balance'], 2)); ?></td>
                                        <?php else: ?>
                                            <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format($section['net_balance'], 2)); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div x-show="view === 'detailed'" x-cloak>
            <div class="mb-3 flex justify-end">
                <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" x-model="compact" class="rounded border-erp-border text-erp-accent focus:ring-erp-accent">
                    <?php echo e(__('Compact mode')); ?>

                </label>
            </div>

            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'overflow-hidden p-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden p-0']); ?>
                <div class="max-h-[min(70vh,48rem)] overflow-auto">
                    <table class="w-full min-w-[40rem] text-sm" :class="compact && 'text-xs'">
                        <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
                            <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-3" :class="compact && 'py-2'"><?php echo e(__('Account')); ?></th>
                                <th class="px-4 py-3 text-right" :class="compact && 'py-2'"><?php echo e(__('Debit')); ?></th>
                                <th class="px-4 py-3 text-right" :class="compact && 'py-2'"><?php echo e(__('Credit')); ?></th>
                                <?php if($tableMode === 'extended'): ?>
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'"><?php echo e(__('Debit bal.')); ?></th>
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'"><?php echo e(__('Credit bal.')); ?></th>
                                <?php else: ?>
                                    <th class="px-4 py-3 text-right" :class="compact && 'py-2'"><?php echo e(__('Balance')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $lastSection = null; ?>
                            <?php $__currentLoopData = $detailedRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $sectionKey = $resolveSection($row['account_code']);
                                    $sectionLabel = $sectionDefs[$sectionKey]['label'] ?? __('Other');
                                ?>
                                <?php if($sectionKey !== $lastSection): ?>
                                    <tr class="bg-slate-100/90">
                                        <td colspan="<?php echo e($tableMode === 'extended' ? 5 : 4); ?>" class="px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            <?php echo e($sectionLabel); ?>

                                        </td>
                                    </tr>
                                    <?php $lastSection = $sectionKey; ?>
                                <?php endif; ?>
                                <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-slate-50/50'); ?> border-b border-erp-border/30 transition-colors hover:bg-erp-accent/5">
                                    <td class="px-4 tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'">
                                        <span class="font-mono text-xs text-slate-500"><?php echo e($row['account_code']); ?></span>
                                        <span class="text-erp-primary"><?php echo e($row['account_name']); ?></span>
                                    </td>
                                    <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'"><?php echo e($row['period_debit'] > 0 ? number_format($row['period_debit'], 2) : '—'); ?></td>
                                    <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'"><?php echo e($row['period_credit'] > 0 ? number_format($row['period_credit'], 2) : '—'); ?></td>
                                    <?php if($tableMode === 'extended'): ?>
                                        <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'"><?php echo e(($row['debit_balance'] ?? 0) > 0 ? number_format($row['debit_balance'], 2) : '—'); ?></td>
                                        <td class="px-4 text-right tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'"><?php echo e(($row['credit_balance'] ?? 0) > 0 ? number_format($row['credit_balance'], 2) : '—'); ?></td>
                                    <?php else: ?>
                                        <td class="px-4 text-right font-medium tabular-nums" :class="compact ? 'py-1.5' : 'py-2.5'"><?php echo e(number_format($rowNetBalance($row), 2)); ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-slate-100 font-semibold text-erp-primary shadow-[0_-1px_0_0_var(--color-erp-border)]">
                            <tr>
                                <td class="px-4 py-3"><?php echo e(__('Totals')); ?></td>
                                <td class="px-4 py-3 text-right tabular-nums"><?php echo e(number_format($report['total_debit'], 2)); ?></td>
                                <td class="px-4 py-3 text-right tabular-nums"><?php echo e(number_format($report['total_credit'], 2)); ?></td>
                                <td colspan="<?php echo e($tableMode === 'extended' ? 2 : 1); ?>" class="px-4 py-3 text-right">
                                    <?php if($report['is_balanced']): ?>
                                        <span class="text-emerald-600"><?php echo e(__('Balanced')); ?></span>
                                    <?php else: ?>
                                        <span class="text-amber-600"><?php echo e(__('Out of balance')); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
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
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\trial-balance-enterprise.blade.php ENDPATH**/ ?>