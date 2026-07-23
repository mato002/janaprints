<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Operations Advisor'),'breadcrumbs' => [
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Operations Advisor')],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Operations Advisor'),'description' => __('Autonomous print operations advisor (PI10). Read-only recommendations — no inventory, accounting, quotation, or production mutations.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Operations Advisor')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Autonomous print operations advisor (PI10). Read-only recommendations — no inventory, accounting, quotation, or production mutations.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php echo $__env->make('admin.printing-intelligence.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
        $tabs = [
            'overview' => __('Overview'),
            'quotations' => __('Quotations'),
            'artwork' => __('Artwork'),
            'machines' => __('Machines'),
            'inventory' => __('Inventory'),
            'customers' => __('Customers'),
            'profitability' => __('Profitability'),
            'forecasts' => __('Forecasts'),
        ];
        $typeMap = [
            'quotations' => 'quotation',
            'artwork' => 'artwork',
            'machines' => 'machine',
            'inventory' => 'inventory',
            'customers' => 'customer',
            'profitability' => 'profitability',
            'forecasts' => 'forecast',
        ];
        $summary = $overview['summary'] ?? [];
        $confidenceBand = fn ($score) => match (true) {
            $score >= 75 => __('High'),
            $score >= 45 => __('Medium'),
            default => __('Low'),
        };
    ?>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'flex-1 min-w-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-1 min-w-0']); ?>
            <nav class="flex flex-wrap gap-2">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('admin.printing-intelligence.operations-advisor', array_merge($filters ?? [], ['tab' => $key]))); ?>"
                       class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                           'rounded-md px-3 py-1.5 text-xs font-medium',
                           'bg-slate-900 text-white' => ($tab ?? 'overview') === $key,
                           'bg-slate-100 text-slate-700 hover:bg-slate-200' => ($tab ?? 'overview') !== $key,
                       ]); ?>">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
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

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.advisor.manage')): ?>
            <form method="post" action="<?php echo e(route('admin.printing-intelligence.advisor.generate')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Generate recommendations')); ?></button>
            </form>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 mb-6">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Open Recommendations'),'value' => (string) ($summary['open'] ?? 0),'icon' => 'bell']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Open Recommendations')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($summary['open'] ?? 0)),'icon' => 'bell']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Critical Alerts'),'value' => (string) ($summary['critical'] ?? 0),'icon' => 'exclamation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Critical Alerts')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($summary['critical'] ?? 0)),'icon' => 'exclamation']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('High Confidence'),'value' => (string) ($summary['high_confidence'] ?? 0),'icon' => 'check-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('High Confidence')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($summary['high_confidence'] ?? 0)),'icon' => 'check-circle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Acknowledged'),'value' => (string) ($summary['acknowledged'] ?? 0),'icon' => 'clipboard-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Acknowledged')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($summary['acknowledged'] ?? 0)),'icon' => 'clipboard-check']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Dismissed'),'value' => (string) ($summary['dismissed'] ?? 0),'icon' => 'archive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dismissed')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($summary['dismissed'] ?? 0)),'icon' => 'archive']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
    </div>

    <?php if(($tab ?? 'overview') === 'overview' && ! empty($executiveSummary)): ?>
        <div class="grid gap-4 lg:grid-cols-2 mb-6">
            <?php $__currentLoopData = [
                'top_opportunities' => __('Top Opportunities'),
                'top_risks' => __('Top Risks'),
                'top_margin_threats' => __('Top Margin Threats'),
                'top_growth_areas' => __('Top Growth Areas'),
                'top_inventory_risks' => __('Top Inventory Risks'),
                'top_capacity_risks' => __('Top Capacity Risks'),
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $heading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                    <h3 class="font-medium mb-3"><?php echo e($heading); ?></h3>
                    <?php $items = $executiveSummary[$key] ?? []; ?>
                    <?php if(empty($items)): ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No open recommendations in this category.')); ?></p>
                    <?php else: ?>
                        <ul class="space-y-2 text-sm">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="rounded border border-slate-200 px-3 py-2">
                                    <span class="erp-badge mr-2"><?php echo e(ucfirst($item->severity?->value ?? 'info')); ?></span>
                                    <strong><?php echo e($item->title); ?></strong>
                                    <p class="text-slate-600 mt-1"><?php echo e($item->summary); ?></p>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php
        $recommendations = ($tab ?? 'overview') === 'overview'
            ? ($overview['recommendations'] ?? collect())
            : (($tabRecommendations ?? collect())->isNotEmpty()
                ? $tabRecommendations
                : collect($liveRecommendations ?? [])->map(fn ($rec) => (object) [
                    'id' => null,
                    'title' => $rec['title'] ?? '',
                    'summary' => $rec['summary'] ?? '',
                    'recommendation_text' => $rec['recommendation_text'] ?? '',
                    'severity' => \App\Enums\AdvisorSeverity::tryFrom($rec['severity']?->value ?? $rec['severity'] ?? 'info'),
                    'recommendation_type' => \App\Enums\AdvisorRecommendationType::tryFrom($rec['recommendation_type']?->value ?? $rec['recommendation_type'] ?? 'quotation'),
                    'confidence_score' => $rec['confidence_score'] ?? 0,
                    'recommended_action' => $rec['recommended_action'] ?? null,
                    'source_module' => $rec['source_module'] ?? '',
                    'status' => \App\Enums\AdvisorRecommendationStatus::Open,
                    'comment' => null,
                ]));
    ?>

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
        <h3 class="font-medium mb-3">
            <?php if(($tab ?? 'overview') === 'overview'): ?>
                <?php echo e(__('Open Recommendations')); ?>

            <?php else: ?>
                <?php echo e(__('Live Advisor Signals')); ?> — <?php echo e($tabs[$tab] ?? ''); ?>

            <?php endif; ?>
        </h3>

        <?php if($recommendations->isEmpty()): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No recommendations yet. Run generation to analyze PI3–PI9 signals.')); ?></p>
        <?php else: ?>
            <?php if(($tab ?? 'overview') !== 'overview' && ($tabRecommendations ?? collect())->isEmpty() && ! empty($liveRecommendations)): ?>
                <p class="mb-3 text-xs text-amber-700"><?php echo e(__('Showing live signals — generate recommendations to persist and acknowledge them.')); ?></p>
            <?php endif; ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-slate-200 p-4 text-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="erp-badge mr-2"><?php echo e(ucfirst($rec->severity?->value ?? 'info')); ?></span>
                                <span class="erp-badge mr-2"><?php echo e(ucfirst($rec->recommendation_type?->value ?? '')); ?></span>
                                <span class="text-xs text-slate-500"><?php echo e($rec->source_module ?? ''); ?></span>
                                <h4 class="font-semibold mt-1"><?php echo e($rec->title); ?></h4>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <?php echo e(__('Confidence')); ?>: <?php echo e(number_format((float) ($rec->confidence_score ?? 0), 0)); ?>

                                (<?php echo e($confidenceBand((float) ($rec->confidence_score ?? 0))); ?>)
                            </div>
                        </div>
                        <p class="text-slate-700"><?php echo e($rec->summary); ?></p>
                        <p class="text-slate-600 mt-2"><?php echo e($rec->recommendation_text); ?></p>
                        <?php if(! empty($rec->recommended_action)): ?>
                            <p class="mt-2 text-xs font-medium text-slate-800"><?php echo e(__('Recommended action')); ?>: <?php echo e($rec->recommended_action); ?></p>
                        <?php endif; ?>

                        <?php if($rec->id && ($rec->status?->value ?? 'open') === 'open'): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.advisor.manage')): ?>
                                <div class="mt-3 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                                    <form method="post" action="<?php echo e(route('admin.printing-intelligence.advisor.acknowledge', $rec->id)); ?>" class="flex flex-wrap items-end gap-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="text" name="comment" placeholder="<?php echo e(__('Comment (optional)')); ?>" class="erp-input text-xs max-w-xs" />
                                        <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Acknowledge')); ?></button>
                                    </form>
                                    <form method="post" action="<?php echo e(route('admin.printing-intelligence.advisor.dismiss', $rec->id)); ?>" class="flex flex-wrap items-end gap-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="text" name="comment" placeholder="<?php echo e(__('Dismiss reason')); ?>" class="erp-input text-xs max-w-xs" />
                                        <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Dismiss')); ?></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php elseif(! empty($rec->comment)): ?>
                            <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Comment')); ?>: <?php echo e($rec->comment); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\operations-advisor.blade.php ENDPATH**/ ?>