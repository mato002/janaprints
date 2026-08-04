<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Maintenance'),'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Maintenance')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Maintenance Operations'),'description' => match ($activeTab) {
            'work-orders' => __('Preventive, corrective, and emergency maintenance work orders.'),
            'plans' => __('Preventive maintenance schedules and upcoming due dates.'),
            'calendar' => __('Scheduled maintenance across month, week, and overdue views.'),
            'downtime' => __('Asset downtime records with duration and impact.'),
            'technicians' => __('Internal and vendor maintenance technicians.'),
            default => __('Work orders, downtime, and preventive maintenance overview.'),
        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Maintenance Operations')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($activeTab) {
            'work-orders' => __('Preventive, corrective, and emergency maintenance work orders.'),
            'plans' => __('Preventive maintenance schedules and upcoming due dates.'),
            'calendar' => __('Scheduled maintenance across month, week, and overdue views.'),
            'downtime' => __('Asset downtime records with duration and impact.'),
            'technicians' => __('Internal and vendor maintenance technicians.'),
            default => __('Work orders, downtime, and preventive maintenance overview.'),
        })]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if($activeTab === 'work-orders'): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Assets\MaintenanceWorkOrder::class)): ?>
                    <a href="<?php echo e(route('admin.assets.maintenance.work-orders.create')); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('New work order')); ?></a>
                <?php endif; ?>
            <?php elseif($activeTab === 'plans'): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Assets\MaintenancePlan::class)): ?>
                    <a href="<?php echo e(route('admin.assets.maintenance.plans.create')); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('New plan')); ?></a>
                <?php endif; ?>
            <?php elseif($activeTab === 'overview'): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Assets\MaintenanceWorkOrder::class)): ?>
                    <a href="<?php echo e(route('admin.assets.maintenance.work-orders.create')); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('New work order')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
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

    <?php echo $__env->make('admin.assets.maintenance.partials.tabs-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('admin.assets.maintenance.partials.tabs.' . match ($activeTab) {
        'work-orders' => 'work_orders',
        default => str_replace('-', '_', $activeTab),
    }, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\maintenance\hub.blade.php ENDPATH**/ ?>