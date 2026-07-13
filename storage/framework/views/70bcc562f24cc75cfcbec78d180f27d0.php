<?php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Leave'),'breadcrumbs' => [['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Leave')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Leave Management'),'description' => __('Leave requests, balances, and workforce absence planning.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Leave Management')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Leave requests, balances, and workforce absence planning.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Hr\LeaveRequest::class)): ?>
                <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.create'))); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Apply for leave')); ?></a>
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

    <?php if(session('status')): ?>
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'requests', 'status' => 'submitted'])))); ?>" data-turbo-frame="<?php echo e($turboFrame); ?>" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Pending Approval'),'value' => $stats['pending'],'icon' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Pending Approval')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['pending']),'icon' => 'clock']); ?>
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
        <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'requests', 'status' => 'approved'])))); ?>" data-turbo-frame="<?php echo e($turboFrame); ?>" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Approved This Month'),'value' => $stats['approved_this_month'],'icon' => 'check-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Approved This Month')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['approved_this_month']),'icon' => 'check-circle']); ?>
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
        <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'calendar', 'view' => 'week'])))); ?>" data-turbo-frame="<?php echo e($turboFrame); ?>" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('On Leave Today'),'value' => $stats['on_leave_today'],'icon' => 'calendar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('On Leave Today')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['on_leave_today']),'icon' => 'calendar']); ?>
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
    </div>

    <nav class="mt-6 mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="<?php echo e(__('Leave sections')); ?>">
        <?php
            $leaveTabs = [
                'requests' => __('All requests'),
                'balances' => __('Leave balances'),
                'calendar' => __('Calendar'),
            ];
            if ($canManageSetup ?? false) {
                $leaveTabs['setup'] = __('Setup');
            }
        ?>
        <?php $__currentLoopData = $leaveTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_filter(['tab' => $id, 'view' => $id === 'calendar' ? ($calendarView ?? 'month') : null]))))); ?>"
                data-turbo-frame="<?php echo e($turboFrame); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'rounded-md px-3 py-1.5 text-sm font-medium',
                    'bg-erp-primary text-white' => $tab === $id,
                    'text-slate-600 hover:bg-slate-100' => $tab !== $id,
                ]); ?>"
            ><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <?php if($tab === 'requests'): ?>
        <?php echo $__env->make('admin.hr.leave.partials.workspace-requests', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'balances'): ?>
        <?php echo $__env->make('admin.hr.leave.partials.workspace-balances', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'calendar'): ?>
        <?php echo $__env->make('admin.hr.leave.partials.workspace-calendar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'setup'): ?>
        <?php echo $__env->make('admin.hr.leave.partials.workspace-setup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/leave/dashboard.blade.php ENDPATH**/ ?>