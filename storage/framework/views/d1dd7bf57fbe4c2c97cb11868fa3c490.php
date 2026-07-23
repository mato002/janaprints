<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Printing Intelligence'),'breadcrumbs' => [
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Overview')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Printing Intelligence'),'description' => __('Unified operational intelligence across artwork, costing, profitability, and forecasting (PI1–PI9). Read-only dashboards.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Printing Intelligence')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Unified operational intelligence across artwork, costing, profitability, and forecasting (PI1–PI9). Read-only dashboards.'))]); ?>
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

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 mb-6">
        <?php $__currentLoopData = [
            ['label' => __('Artwork Analyses'), 'value' => $metrics['artwork_analyses'] ?? 0, 'icon' => 'photograph', 'route' => 'admin.printing-intelligence.artwork-analysis.index'],
            ['label' => __('Ink Estimates'), 'value' => $metrics['ink_estimates'] ?? 0, 'icon' => 'color-swatch', 'route' => 'admin.printing-intelligence.ink'],
            ['label' => __('Machine Estimates'), 'value' => $metrics['machine_estimates'] ?? 0, 'icon' => 'cog', 'route' => 'admin.printing-intelligence.machines'],
            ['label' => __('Quotation Estimates'), 'value' => $metrics['quotation_estimates'] ?? 0, 'icon' => 'document-text', 'route' => 'admin.printing-intelligence.quotations'],
            ['label' => __('Estimate Accuracy'), 'value' => ($metrics['estimate_accuracy'] ?? null) !== null ? number_format((float) $metrics['estimate_accuracy'], 1).'%' : '—', 'icon' => 'check-circle', 'route' => 'admin.printing-intelligence.estimate-vs-actual', 'permission' => 'printing.estimate-actual.view'],
            ['label' => __('Total Profit (90d)'), 'value' => ($metrics['total_profit'] ?? null) !== null ? number_format((float) $metrics['total_profit'], 2) : '—', 'icon' => 'chart-bar', 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
            ['label' => __('Average Margin'), 'value' => ($metrics['average_margin'] ?? null) !== null ? number_format((float) $metrics['average_margin'], 1).'%' : '—', 'icon' => 'percent', 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
            ['label' => __('Forecast Confidence'), 'value' => ($metrics['forecast_confidence'] ?? null) !== null ? number_format((float) $metrics['forecast_confidence'], 1).'%' : '—', 'icon' => 'scale', 'route' => 'admin.printing-intelligence.executive-intelligence', 'permission' => 'printing.executive.view'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($card['permission']) || auth()->user()?->can($card['permission'])): ?>
                <a href="<?php echo e(route($card['route'])); ?>" class="block rounded-lg border border-slate-200 bg-white p-3 hover:border-sky-300 transition-colors">
                    <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $card['label'],'value' => $card['value'],'icon' => $card['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon'])]); ?>
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
                </a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8 mb-6">
        <?php $__currentLoopData = [
            ['label' => __('Materials tracked'), 'value' => $metrics['materials_tracked'], 'icon' => 'cube'],
            ['label' => __('Ink profiles'), 'value' => $metrics['ink_profiles'], 'icon' => 'color-swatch'],
            ['label' => __('Machine profiles'), 'value' => $metrics['machine_profiles'], 'icon' => 'cog'],
            ['label' => __('Items with cost data'), 'value' => $metrics['items_with_cost_data'], 'icon' => 'currency-dollar'],
            ['label' => __('Items with velocity'), 'value' => $metrics['items_with_velocity_data'], 'icon' => 'switch-horizontal'],
            ['label' => __('Stockout risk items'), 'value' => $metrics['items_at_stockout_risk'], 'icon' => 'exclamation'],
            ['label' => __('Dead stock value'), 'value' => number_format($metrics['dead_stock_value'], 2), 'icon' => 'archive'],
            ['label' => __('Avg inventory health'), 'value' => $metrics['average_inventory_health'] !== null ? $metrics['average_inventory_health'].'%' : '—', 'icon' => 'chart-bar'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $card['label'],'value' => $card['value'],'icon' => $card['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon'])]); ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

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
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Quick links')); ?></h3>
        <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = [
                ['label' => __('Artwork Analysis'), 'route' => 'admin.printing-intelligence.artwork-analysis.index'],
                ['label' => __('Machine Intelligence'), 'route' => 'admin.printing-intelligence.machines'],
                ['label' => __('Ink Intelligence'), 'route' => 'admin.printing-intelligence.ink'],
                ['label' => __('Material Intelligence'), 'route' => 'admin.printing-intelligence.material'],
                ['label' => __('Cost Intelligence'), 'route' => 'admin.printing-intelligence.cost'],
                ['label' => __('Quotation Intelligence'), 'route' => 'admin.printing-intelligence.quotations'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($link['route'])); ?>" class="erp-btn-secondary text-xs"><?php echo e($link['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.estimate-actual.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.estimate-vs-actual')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Estimate vs Actual')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.calibration.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.calibration-governance')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Calibration Governance')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.profitability.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.production-profitability')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Production Profitability')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.executive.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.executive-intelligence')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Executive Intelligence')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.advisor.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.operations-advisor')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Operations Advisor')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.ink-profiles.view')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.ink-profiles.index')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Ink Profiles')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.intelligence.configure')): ?>
                <a href="<?php echo e(route('admin.printing-intelligence.configuration')); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Configuration')); ?></a>
            <?php endif; ?>
        </div>
        <p class="mt-4 text-xs text-slate-500"><?php echo e(__('Read-only intelligence — no inventory, accounting, or production mutations from this workspace.')); ?></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/printing-intelligence/overview.blade.php ENDPATH**/ ?>