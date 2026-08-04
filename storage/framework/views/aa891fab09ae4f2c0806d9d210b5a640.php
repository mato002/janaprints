<?php
    use App\Support\Production\ProductionDeskPersona;
    use App\Support\Production\ProductionFloorDeskViews;

    $pageTitle = $active_department_label
        ? __(':department queue', ['department' => $active_department_label])
        : __('By department');
    $indexRoute = ProductionFloorDeskViews::queueIndexUrl($active_department ?: null);
    $commandMetrics = $command_metrics ?? $metrics ?? [];
    $persona = $deskPersona ?? ProductionDeskPersona::resolve(auth()->user());
    $hideDepartmentPills = $persona->usesDepartmentOperationsModes() && ($embeddedInFloor ?? false);
    $dense = (bool) ($embeddedInFloor ?? false);
    $useRibbon = $dense || $persona->usesDepartmentOperationsModes();

    $departmentTabs = [];
    if ($useRibbon) {
        if ($hideDepartmentPills) {
            $departmentTabs = collect($persona->standaloneFloorModes($active_department ?? null))
                ->reject(fn (array $mode) => $mode['key'] === ProductionFloorDeskViews::FLOOR)
                ->map(fn (array $mode) => [
                    'key' => $mode['key'],
                    'label' => $mode['label'],
                    'url' => $mode['url'],
                    'active' => ($active_department ?? null) === $mode['key'],
                ])
                ->values()
                ->all();
        } else {
            $departmentTabs = collect($department_nav ?? [])
                ->reject(fn (array $item) => ($item['slug'] ?? '') === '')
                ->map(fn (array $item) => [
                    'key' => $item['slug'],
                    'label' => $item['label'],
                    'url' => $item['url'],
                    'active' => $item['active'] ?? false,
                ])
                ->values()
                ->all();
        }
    }
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'production-queue-workspace flex min-h-0 flex-1 flex-col',
    'production-queue-workspace--dense' => $dense,
    'production-queue-workspace--ribbon' => $useRibbon,
]); ?>">
    <?php if (! ($dense || $useRibbon)): ?>
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $pageTitle,'description' => __('Daily department jobs — defaults to jobs logged today.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pageTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Daily department jobs — defaults to jobs logged today.'))]); ?>
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
    <?php endif; ?>

    <?php if($useRibbon): ?>
        <?php echo $__env->make('admin.production.queue.partials.production-queue-ribbon', [
            'departmentTabs' => $departmentTabs,
            'commandMetrics' => $commandMetrics,
            'summary' => $summary ?? [],
            'filters' => $filters ?? [],
            'activeDepartment' => $active_department ?? null,
            'indexRoute' => $indexRoute,
            'workCenters' => $workCenters,
            'operators' => $operators,
            'machines' => $machines,
            'customers' => $customers,
            'workspace' => $workspace,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('admin.production.queue.partials.department-nav', [
            'departmentNav' => $department_nav,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="production-queue-workspace__chrome sticky top-0 z-20 shrink-0 bg-erp-page">
            <?php echo $__env->make('admin.production.queue.partials.metrics-strip', [
                'metrics' => $commandMetrics,
                'compact' => $dense,
                'summary' => $summary ?? [],
                'filters' => $filters ?? [],
                'activeDepartment' => $active_department ?? null,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="overflow-hidden rounded-md border border-erp-border bg-white shadow-sm">
                <?php echo $__env->make('admin.production.queue.partials.toolbar', [
                    'indexRoute' => $indexRoute,
                    'filters' => $filters,
                    'workCenters' => $workCenters,
                    'operators' => $operators,
                    'machines' => $machines,
                    'customers' => $customers,
                    'workspace' => $workspace,
                    'activeDepartment' => $active_department,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="production-queue-workspace__table mt-2 min-h-0 flex-1 overflow-hidden rounded-md border border-erp-border bg-white shadow-sm">
        <?php echo $__env->make('admin.production.queue.partials.table', [
            'queues' => $queues,
            'workspace' => $workspace,
            'commandCenter' => $command_center ?? null,
            'columns' => $columns ?? [],
            'activeDepartment' => $active_department,
            'dense' => $dense,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if (! ($dense)): ?>
        <?php echo $__env->make('admin.production.queue.partials.summary', [
            'summary' => $summary ?? [],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\workspace-content.blade.php ENDPATH**/ ?>