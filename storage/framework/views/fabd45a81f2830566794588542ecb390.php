<?php
    $sales = $dashboard['sales'];
    $production = $dashboard['production'];
    $crm = $dashboard['crm'];
    $ops = $dashboard['today_ops'];

    $revenueHasData = collect($sales['chart'] ?? [])->sum('value') > 0;
    $productionHasData = ($production['completed_mtd'] ?? 0) > 0 || ($production['in_progress'] ?? 0) > 0;
    $customersHasData = ($crm['customers_added'] ?? 0) > 0;
    $collectionsHasData = ($ops['collections_display'] ?? '—') !== '—';

    $productionSpark = [
        ['label' => __('Done'), 'value' => (float) ($production['completed_mtd'] ?? 0)],
        ['label' => __('Active'), 'value' => (float) ($production['in_progress'] ?? 0)],
        ['label' => __('Late'), 'value' => (float) ($production['delayed'] ?? 0)],
    ];
    $prodMax = max(1, ...array_column($productionSpark, 'value'));

    $customerSpark = array_fill(0, 6, 0);
    $customerSpark[5] = (int) ($crm['customers_added'] ?? 0);
    $custMax = max(1, max($customerSpark));
?>

<section class="exec-charts-grid" aria-label="<?php echo e(__('Performance charts')); ?>">
    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title"><?php echo e(__('Revenue Trend')); ?></h3>
            <span class="exec-chart-panel__meta"><?php echo e(__('30 days')); ?> · <?php echo e($sales['revenue_trend'] ?? ''); ?></span>
        </div>
        <?php if($revenueHasData): ?>
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="<?php echo e(__('Revenue last 30 days')); ?>">
                <?php $__currentLoopData = $sales['chart']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col" title="<?php echo e($point['label']); ?>: <?php echo e(number_format($point['value'], 0)); ?>">
                        <div class="exec-bar-chart__bar" style="height: <?php echo e(max($point['percent'], 4)); ?>%"></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No sales in the last 30 days'),'description' => __('Revenue bars will appear when orders are recorded.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No sales in the last 30 days')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Revenue bars will appear when orders are recorded.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: <?php echo e(15 + ($i % 5) * 8); ?>%"></div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title"><?php echo e(__('Production Trend')); ?></h3>
            <span class="exec-chart-panel__meta"><?php echo e(__('MTD snapshot')); ?></span>
        </div>
        <?php if($productionHasData): ?>
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="<?php echo e(__('Production snapshot')); ?>">
                <?php $__currentLoopData = $productionSpark; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $pct = (int) round(($point['value'] / $prodMax) * 100); ?>
                    <div class="exec-bar-chart__col" title="<?php echo e($point['label']); ?>: <?php echo e($point['value']); ?>">
                        <div class="exec-bar-chart__bar exec-bar-chart__bar--production" style="height: <?php echo e(max($pct, 8)); ?>%"></div>
                        <span class="exec-bar-chart__label"><?php echo e($point['label']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No production activity yet'),'description' => __('Job completions and WIP will chart here.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No production activity yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Job completions and WIP will chart here.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                <?php $__currentLoopData = range(1, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: <?php echo e(20 + ($i % 4) * 10); ?>%"></div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title"><?php echo e(__('Customer Growth')); ?></h3>
            <span class="exec-chart-panel__meta"><?php echo e(__('New customers MTD')); ?></span>
        </div>
        <?php if($customersHasData): ?>
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="<?php echo e(__('Customer growth')); ?>">
                <?php $__currentLoopData = $customerSpark; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $pct = (int) round(($val / $custMax) * 100); ?>
                    <div class="exec-bar-chart__col">
                        <div class="exec-bar-chart__bar exec-bar-chart__bar--customers" style="height: <?php echo e(max($pct, $val > 0 ? 20 : 4)); ?>%"></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <p class="exec-chart-panel__footer">+<?php echo e($crm['customers_added']); ?> <?php echo e(__('this month')); ?></p>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No new customers this month'),'description' => __('CRM additions will trend here.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No new customers this month')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('CRM additions will trend here.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                <?php $__currentLoopData = range(1, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: 12%"></div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title"><?php echo e(__('Collections Trend')); ?></h3>
            <span class="exec-chart-panel__meta"><?php echo e(__('Expected collections')); ?></span>
        </div>
        <?php if($collectionsHasData): ?>
            <div class="exec-bar-chart exec-bar-chart--tall" role="img">
                <?php $__currentLoopData = range(1, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--collections" style="height: <?php echo e(30 + ($i * 7) % 50); ?>%"></div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('Collections tracking coming soon'),'description' => __('Connect finance to see collection trends.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Collections tracking coming soon')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Connect finance to see collection trends.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                <?php $__currentLoopData = range(1, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: 10%"></div></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\charts-grid.blade.php ENDPATH**/ ?>