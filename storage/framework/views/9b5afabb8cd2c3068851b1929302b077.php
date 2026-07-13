<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php echo $__env->make('admin.hr.attendance.partials.filters', [
    'filters' => $filters,
    'formData' => $formData,
    'statuses' => $statuses,
    'action' => route('admin.hr.attendance.dashboard'),
    'exportAction' => route('admin.hr.attendance.export'),
    'canExport' => auth()->user()?->can('export', App\Models\Hr\AttendanceRecord::class) ?? false,
    'hidden' => array_filter([
        'tab' => 'register',
        'embedded' => WorkspaceEmbed::inWorkspaceContext() ? '1' : null,
    ]),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['searchPlaceholder' => __('Search attendance…'),'exportFilename' => 'attendance-register','exportable' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search attendance…')),'export-filename' => 'attendance-register','exportable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
     <?php $__env->slot('head', null, []); ?> 
        <tr>
            <th scope="col"><?php echo e(__('Employee')); ?></th>
            <th scope="col" class="hidden md:table-cell"><?php echo e(__('Employee Number')); ?></th>
            <th scope="col" class="hidden lg:table-cell"><?php echo e(__('Department')); ?></th>
            <th scope="col" class="hidden lg:table-cell"><?php echo e(__('Branch')); ?></th>
            <th scope="col" class="hidden xl:table-cell"><?php echo e(__('Shift')); ?></th>
            <th scope="col"><?php echo e(__('Clock In')); ?></th>
            <th scope="col"><?php echo e(__('Clock Out')); ?></th>
            <th scope="col" class="hidden md:table-cell"><?php echo e(__('Hours')); ?></th>
            <th scope="col" class="hidden md:table-cell"><?php echo e(__('Overtime')); ?></th>
            <th scope="col"><?php echo e(__('Status')); ?></th>
            <th scope="col" class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
        </tr>
     <?php $__env->endSlot(); ?>
     <?php $__env->slot('body', null, []); ?> 
        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $searchText = strtolower(implode(' ', array_filter([
                    $record->employee?->full_name,
                    $record->employee?->employee_number,
                    $record->department?->name,
                    $record->branch?->name,
                    $record->shift?->name,
                    $record->status?->label(),
                ])));
            ?>
            <tr x-show="rowVisible(<?php echo \Illuminate\Support\Js::from($searchText)->toHtml() ?>)">
                <td>
                    <div class="font-medium text-erp-primary"><?php echo e($record->employee?->full_name); ?></div>
                    <div class="text-[11px] text-slate-500"><?php echo e($record->attendance_date?->format('Y-m-d')); ?></div>
                </td>
                <td class="hidden md:table-cell erp-ref-code"><?php echo e($record->employee?->employee_number); ?></td>
                <td class="hidden lg:table-cell"><?php echo e($record->department?->name ?? '—'); ?></td>
                <td class="hidden lg:table-cell"><?php echo e($record->branch?->name ?? '—'); ?></td>
                <td class="hidden xl:table-cell"><?php echo e($record->shift?->name ?? '—'); ?></td>
                <td class="tabular-nums text-sm"><?php echo e($record->clock_in_at?->format('H:i') ?? '—'); ?></td>
                <td class="tabular-nums text-sm"><?php echo e($record->clock_out_at?->format('H:i') ?? '—'); ?></td>
                <td class="hidden md:table-cell tabular-nums"><?php echo e($record->actual_hours ?? '—'); ?></td>
                <td class="hidden md:table-cell tabular-nums"><?php echo e($record->overtime_hours); ?></td>
                <td>
                    <span class="erp-badge erp-badge--<?php echo e($record->status?->badgeClass()); ?>">
                        <?php echo e($record->status?->label()); ?>

                    </span>
                </td>
                <td class="erp-table-actions-col">
                    <?php if (isset($component)) { $__componentOriginalb5a89013017505cf4d4d69115d724d07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5a89013017505cf4d4d69115d724d07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('adjust', $record)): ?>
                            <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => WorkspaceEmbed::url(route('admin.hr.attendance.adjust', $record))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(WorkspaceEmbed::url(route('admin.hr.attendance.adjust', $record)))]); ?><?php echo e(__('Adjust')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                        <?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $attributes = $__attributesOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__attributesOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $component = $__componentOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__componentOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="11"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'clock','title' => __('No attendance records')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clock','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No attendance records'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td></tr>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>
     <?php $__env->slot('footer', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $records]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($records)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $attributes = $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $component = $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/attendance/partials/workspace-register.blade.php ENDPATH**/ ?>