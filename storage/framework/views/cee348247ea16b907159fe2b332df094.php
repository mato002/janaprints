<?php
    use App\Services\Production\ProductionJobCardIndexService;

    $indexService = app(ProductionJobCardIndexService::class);
    $jobCards = $job_cards ?? null;
    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $activeChips = $active_filter_chips ?? [];
    $statusTabs = $status_tabs ?? [];
    $savedViewPresets = $saved_view_presets ?? [];
    $registerColumns = $register_columns ?? [];
    $bulkActions = $bulk_actions ?? [];
    $hasActiveFilters = $has_active_filters ?? false;
    $canCreate = $can_create ?? false;
    $createUrl = $create_url ?? null;
    $salesOrdersUrl = $sales_orders_url ?? null;
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Job cards'),'breadcrumbs' => [
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Job cards')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Job Cards'),'description' => __('Production order execution register.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Job Cards')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production order execution register.'))]); ?>
        <?php if($canCreate && $createUrl): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e($createUrl); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Create Job Card')); ?></a>
             <?php $__env->endSlot(); ?>
        <?php endif; ?>
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

    <div
        x-data="jobCardsRegister(<?php echo \Illuminate\Support\Js::from([
            'columns' => $registerColumns,
            'presets' => $savedViewPresets,
            'indexUrl' => route('admin.production.job-cards.index'),
        ])->toHtml() ?>)"
    >
        <?php echo $__env->make('admin.production.job-cards.register.filters', [
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'activeChips' => $activeChips,
            'statusTabs' => $statusTabs,
            'savedViewPresets' => $savedViewPresets,
            'registerColumns' => $registerColumns,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.register.table', [
            'jobCards' => $jobCards,
            'indexService' => $indexService,
            'filters' => $filters,
            'bulkActions' => $bulkActions,
            'registerColumns' => $registerColumns,
            'hasActiveFilters' => $hasActiveFilters,
            'canCreate' => $canCreate,
            'createUrl' => $createUrl,
            'salesOrdersUrl' => $salesOrdersUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/index.blade.php ENDPATH**/ ?>