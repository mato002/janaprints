<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $title] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if($empty_hub ?? false): ?>
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $title,'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'document-text','title' => __('No commercial reports available'),'description' => __('Your role does not include departmental commercial report permissions. Use Commercial 360 or Commercial Intelligence tabs for summary analytics.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No commercial reports available')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Your role does not include departmental commercial report permissions. Use Commercial 360 or Commercial Intelligence tabs for summary analytics.'))]); ?>
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
    <?php else: ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $title,'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php echo $__env->make('admin.commercial.reports.'.$report_view_key.'.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
                'export_route' => $export_route ?? null,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
         <?php $__env->endSlot(); ?>
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

    <?php echo $__env->make('admin.commercial.reports.partials.export-status', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php switch($report_key):
        case ('sales'): ?>
            <?php echo $__env->make('admin.commercial.reports.sales.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('quotations'): ?>
            <?php echo $__env->make('admin.commercial.reports.quotations.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('sales_orders'): ?>
            <?php echo $__env->make('admin.commercial.reports.sales-orders.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('customers'): ?>
            <?php echo $__env->make('admin.commercial.reports.customers.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('artwork'): ?>
            <?php echo $__env->make('admin.commercial.reports.artwork.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'designers' => $designers,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('conversion'): ?>
            <?php echo $__env->make('admin.commercial.reports.conversion.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'lead_sources' => $lead_sources,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
    <?php endswitch; ?>

    <?php echo $__env->make('admin.commercial.reports.'.$report_view_key.'.partials.kpi-strip', ['kpis' => $kpis], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('admin.commercial.reports.'.$report_view_key.'.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
        'index_route' => $index_route ?? null,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php switch($report_key):
        case ('sales'): ?>
            <?php echo $__env->make('admin.commercial.reports.sales.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('quotations'): ?>
            <?php echo $__env->make('admin.commercial.reports.quotations.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('sales_orders'): ?>
            <?php echo $__env->make('admin.commercial.reports.sales-orders.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('customers'): ?>
            <?php echo $__env->make('admin.commercial.reports.customers.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('artwork'): ?>
            <?php echo $__env->make('admin.commercial.reports.artwork.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
        <?php case ('conversion'): ?>
            <?php echo $__env->make('admin.commercial.reports.conversion.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
                'has_production_pipeline' => $has_production_pipeline ?? false,
                'has_dispatch_pipeline' => $has_dispatch_pipeline ?? false,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php break; ?>
    <?php endswitch; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/reports/commercial-hub.blade.php ENDPATH**/ ?>