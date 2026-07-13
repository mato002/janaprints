<?php
    $kpis = collect($dashboard['kpi_strip'])->keyBy('key');
    $sales = $dashboard['sales'];
    $crm = $dashboard['crm'];
    $ops = $dashboard['today_ops'];
    $finance = $dashboard['finance'];
    $inventory = $dashboard['inventory'];
    $complaints = collect($dashboard['attention'])->firstWhere('key', 'complaints');

    $chips = [
        [
            'label' => __('Quotes'),
            'value' => $kpis->get('open_quotes')['value'] ?? '0',
            'route' => $kpis->get('open_quotes')['route'] ?? null,
        ],
        [
            'label' => __('Orders'),
            'value' => (string) ($sales['orders_mtd'] ?? 0),
            'route' => 'admin.sales-orders.index',
        ],
        [
            'label' => __('Customers'),
            'value' => '+'.($crm['customers_added'] ?? 0),
            'route' => 'admin.crm.customers.index',
        ],
        [
            'label' => __('Deliveries'),
            'value' => (string) ($ops['deliveries_today'] ?? 0),
            'route' => 'admin.sales-orders.index',
        ],
        [
            'label' => __('Payables'),
            'value' => $finance['payables'] ?? '—',
            'route' => null,
        ],
        [
            'label' => __('Inventory'),
            'value' => (string) ($inventory['reorder_alerts'] ?? 0).' '.__('alerts'),
            'route' => 'admin.inventory.dashboard',
        ],
        [
            'label' => __('Complaints'),
            'value' => (string) ($complaints['count'] ?? 0),
            'route' => $complaints['route'] ?? null,
        ],
        [
            'label' => __('Machine Utilization'),
            'value' => ($ops['machine_utilization'] ?? 0).'%',
            'route' => 'admin.production.job-cards.index',
        ],
    ];
?>

<section class="exec-health-strip" aria-label="<?php echo e(__('Business health')); ?>">
    <?php $__currentLoopData = $chips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $href = ! empty($chip['route']) && Route::has($chip['route']) ? route($chip['route']) : null;
        ?>
        <?php if (isset($component)) { $__componentOriginalbc8c7e4204fbdccafc89b6bd34c8e5db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbc8c7e4204fbdccafc89b6bd34c8e5db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-health-chip','data' => ['label' => $chip['label'],'value' => $chip['value'],'href' => $href]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-health-chip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($chip['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($chip['value']),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($href)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbc8c7e4204fbdccafc89b6bd34c8e5db)): ?>
<?php $attributes = $__attributesOriginalbc8c7e4204fbdccafc89b6bd34c8e5db; ?>
<?php unset($__attributesOriginalbc8c7e4204fbdccafc89b6bd34c8e5db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbc8c7e4204fbdccafc89b6bd34c8e5db)): ?>
<?php $component = $__componentOriginalbc8c7e4204fbdccafc89b6bd34c8e5db; ?>
<?php unset($__componentOriginalbc8c7e4204fbdccafc89b6bd34c8e5db); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/dashboard/partials/health-strip.blade.php ENDPATH**/ ?>