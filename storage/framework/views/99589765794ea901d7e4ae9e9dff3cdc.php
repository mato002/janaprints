<?php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a
        href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_merge($filters ?? [], ['tab' => 'calendar', 'view' => 'month', 'year' => $year, 'month' => $month]))))); ?>"
        data-turbo-frame="<?php echo e($turboFrame); ?>"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-btn-secondary', 'ring-2 ring-erp-accent' => ($calendarView ?? 'month') === 'month']); ?>"
    ><?php echo e(__('Monthly')); ?></a>
    <a
        href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(array_merge($filters ?? [], ['tab' => 'calendar', 'view' => 'week', 'week' => $weekStart->toDateString()]))))); ?>"
        data-turbo-frame="<?php echo e($turboFrame); ?>"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-btn-secondary', 'ring-2 ring-erp-accent' => ($calendarView ?? 'month') === 'week']); ?>"
    ><?php echo e(__('Weekly')); ?></a>
    <span class="ml-auto text-sm text-slate-500"><?php echo e($periodLabel); ?></span>
</div>

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
    <form method="GET" action="<?php echo e(route('admin.hr.leave.dashboard')); ?>" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-index-toolbar-form">
        <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
            <div class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="calendar">
                <input type="hidden" name="view" value="<?php echo e($calendarView); ?>">
                <?php if(WorkspaceEmbed::inWorkspaceContext()): ?>
                    <input type="hidden" name="embedded" value="1">
                <?php endif; ?>
                <?php if(($calendarView ?? 'month') === 'month'): ?>
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
                <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.dashboard', WorkspaceEmbed::queryParams(['tab' => 'calendar'])))); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="<?php echo e($turboFrame); ?>"><?php echo e(__('Reset')); ?></a>
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

<div class="grid gap-2 <?php if(($calendarView ?? 'month') === 'month'): ?> sm:grid-cols-7 <?php else: ?> grid-cols-1 <?php endif; ?>">
    <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($day['requests']->isNotEmpty()): ?>
            <div class="erp-card p-3 <?php if(($calendarView ?? 'month') === 'month'): ?> min-h-[6rem] <?php endif; ?>">
                <p class="mb-2 text-xs font-semibold text-slate-500"><?php echo e(\Illuminate\Support\Carbon::parse($day['date'])->format('D, M j')); ?></p>
                <div class="space-y-1">
                    <?php $__currentLoopData = $day['requests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.leave.show', $leaveRequest))); ?>" class="block rounded bg-erp-page px-2 py-1 text-xs hover:bg-erp-accent/10">
                            <span class="font-medium"><?php echo e($leaveRequest->employee?->full_name); ?></span>
                            <span class="text-slate-500"> · <?php echo e($leaveRequest->leaveType?->name); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php elseif(($calendarView ?? 'month') === 'week'): ?>
            <div class="erp-card p-3 text-sm text-slate-400"><?php echo e(\Illuminate\Support\Carbon::parse($day['date'])->format('l, M j')); ?> — <?php echo e(__('No leave')); ?></div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\leave\partials\workspace-calendar.blade.php ENDPATH**/ ?>