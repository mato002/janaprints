<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Leave Calendar'),'breadcrumbs' => [['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Calendar')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Leave Calendar'),'description' => $periodLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Leave Calendar')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($periodLabel)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.hr.leave.calendar', array_merge($filters, ['view' => 'month', 'year' => $year, 'month' => $month]))); ?>" class="erp-btn-secondary <?php if($view === 'month'): ?> ring-2 ring-erp-accent <?php endif; ?>"><?php echo e(__('Monthly')); ?></a>
            <a href="<?php echo e(route('admin.hr.leave.calendar', array_merge($filters, ['view' => 'week', 'week' => $weekStart->toDateString()]))); ?>" class="erp-btn-secondary <?php if($view === 'week'): ?> ring-2 ring-erp-accent <?php endif; ?>"><?php echo e(__('Weekly')); ?></a>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
        <form method="GET" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-index-toolbar-form">
            <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="view" value="<?php echo e($view); ?>">
                    <?php if($view === 'month'): ?>
                        <input type="hidden" name="year" value="<?php echo e($year); ?>">
                        <input type="hidden" name="month" value="<?php echo e($month); ?>">
                    <?php else: ?>
                        <input type="hidden" name="week" value="<?php echo e($weekStart->toDateString()); ?>">
                    <?php endif; ?>
                    <select name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>">
                        <option value=""><?php echo e(__('All branches')); ?></option>
                        <?php $__currentLoopData = $formData['branches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php if((int) ($filters['branch_id'] ?? 0) === $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="department_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Department')); ?>">
                        <option value=""><?php echo e(__('All departments')); ?></option>
                        <?php $__currentLoopData = $formData['departments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>" <?php if((int) ($filters['department_id'] ?? 0) === $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <a href="<?php echo e(route('admin.hr.leave.calendar')); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="erp-main"><?php echo e(__('Reset')); ?></a>
                </div>
            </div>
        </form>
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

    <div class="grid gap-2 <?php if($view === 'month'): ?> sm:grid-cols-7 <?php else: ?> grid-cols-1 <?php endif; ?>">
        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($day['requests']->isNotEmpty()): ?>
                <div class="erp-card p-3 <?php if($view === 'month'): ?> min-h-[6rem] <?php endif; ?>">
                    <p class="text-xs font-semibold text-slate-500 mb-2"><?php echo e(\Illuminate\Support\Carbon::parse($day['date'])->format('D, M j')); ?></p>
                    <div class="space-y-1">
                        <?php $__currentLoopData = $day['requests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('admin.hr.leave.show', $leaveRequest)); ?>" class="block rounded bg-erp-page px-2 py-1 text-xs hover:bg-erp-accent/10">
                                <span class="font-medium"><?php echo e($leaveRequest->employee?->full_name); ?></span>
                                <span class="text-slate-500"> · <?php echo e($leaveRequest->leaveType?->name); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php elseif($view === 'week'): ?>
                <div class="erp-card p-3 text-sm text-slate-400"><?php echo e(\Illuminate\Support\Carbon::parse($day['date'])->format('l, M j')); ?> — <?php echo e(__('No leave')); ?></div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/leave/calendar.blade.php ENDPATH**/ ?>