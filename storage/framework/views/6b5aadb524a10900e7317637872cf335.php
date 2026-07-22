<?php
    $panels = [
        ['key' => 'revenue', 'title' => __('Revenue Trend'), 'meta' => __('Ledger MTD months'), 'class' => ''],
        ['key' => 'expenses', 'title' => __('Expense Trend'), 'meta' => __('Ledger MTD months'), 'class' => 'exec-bar-chart__bar--production'],
        ['key' => 'cash_flow', 'title' => __('Cash Flow Trend'), 'meta' => __('Cash accounts'), 'class' => 'exec-bar-chart__bar--collections'],
    ];
?>
<section class="exec-charts-grid exec-charts-grid--finance" aria-label="<?php echo e(__('Finance trends')); ?>">
    <?php $__currentLoopData = $panels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $panel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $chart = $trends[$panel['key']] ?? [];
            $hasData = collect($chart)->sum('value') !== 0.0;
        ?>
        <div class="exec-chart-panel">
            <div class="exec-chart-panel__head">
                <h3 class="exec-chart-panel__title"><?php echo e($panel['title']); ?></h3>
                <span class="exec-chart-panel__meta"><?php echo e($panel['meta']); ?></span>
            </div>
            <?php if($hasData): ?>
                <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="<?php echo e($panel['title']); ?>">
                    <?php $__currentLoopData = $chart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="exec-bar-chart__col" title="<?php echo e($point['label']); ?>: <?php echo e(number_format($point['value'], 0)); ?>">
                            <div class="exec-bar-chart__bar <?php echo e($panel['class']); ?>" style="height: <?php echo e(max($point['percent'] ?? 4, 4)); ?>%"></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No ledger activity yet'),'description' => __('Trend bars appear when journals are posted.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No ledger activity yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Trend bars appear when journals are posted.')),'compact' => true]); ?>
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
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\finance-trends.blade.php ENDPATH**/ ?>