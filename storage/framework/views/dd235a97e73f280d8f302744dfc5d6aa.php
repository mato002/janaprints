<?php
    use App\Services\Production\ProductionJobCardIndexService;
    use App\Support\Production\ProductionFloorDeskViews;

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
    $indexUrl = ProductionFloorDeskViews::registerIndexUrl();
?>

<?php if(! ($embeddedInFloor ?? false)): ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Job Cards'),'description' => __('Full production order register with filters and exports.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Job Cards')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Full production order register with filters and exports.'))]); ?>
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
<?php else: ?>
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Job register')); ?></h2>
            <p class="text-xs text-slate-600"><?php echo e(__('Full production order register with filters and exports.')); ?></p>
        </div>
        <?php if($canCreate && $createUrl): ?>
            <a href="<?php echo e($createUrl); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Create Job Card')); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div
    x-data="jobCardsRegister(<?php echo \Illuminate\Support\Js::from([
        'columns' => $registerColumns,
        'presets' => $savedViewPresets,
        'indexUrl' => $indexUrl,
    ])->toHtml() ?>)"
>
    <?php echo $__env->make('admin.production.job-cards.register.filters', [
        'filters' => $filters,
        'filterOptions' => $filterOptions,
        'activeChips' => $activeChips,
        'statusTabs' => $statusTabs,
        'savedViewPresets' => $savedViewPresets,
        'registerColumns' => $registerColumns,
        'indexUrl' => $indexUrl,
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/partials/register-content.blade.php ENDPATH**/ ?>