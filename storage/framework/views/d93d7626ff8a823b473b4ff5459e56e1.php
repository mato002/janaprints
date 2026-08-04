<?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['exportFilename' => 'payroll-employees']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-filename' => 'payroll-employees']); ?>
     <?php $__env->slot('head', null, []); ?> 
        <tr>
            <th><?php echo e(__('Employee')); ?></th>
            <th><?php echo e(__('Branch')); ?></th>
            <th><?php echo e(__('Gross')); ?></th>
            <th><?php echo e(__('Net')); ?></th>
            <th><?php echo e(__('Days')); ?></th>
            <th><?php echo e(__('Status')); ?></th>
        </tr>
     <?php $__env->endSlot(); ?>
     <?php $__env->slot('body', null, []); ?> 
        <?php $__empty_1 = true; $__currentLoopData = $employees['lines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="font-medium">
                    <?php echo e($line['employee']?->full_name); ?>

                    <span class="block text-xs text-slate-500"><?php echo e($line['employee']?->employee_number); ?></span>
                </td>
                <td><?php echo e($line['employee']?->branch?->name ?? '—'); ?></td>
                <td class="tabular-nums"><?php echo e(number_format($line['payslip']->gross_pay, 2)); ?></td>
                <td class="tabular-nums font-medium"><?php echo e(number_format($line['payslip']->net_pay, 2)); ?></td>
                <td class="text-sm text-slate-500"><?php echo e($line['payslip']->days_worked); ?>W / <?php echo e($line['payslip']->leave_days); ?>L</td>
                <td>
                    <?php if($line['has_warning']): ?>
                        <span class="erp-badge erp-badge--warning"><?php echo e(__('Warning')); ?></span>
                    <?php else: ?>
                        <span class="erp-badge erp-badge--success"><?php echo e(__('OK')); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6">
                    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No employee lines'),'description' => __('Generate payroll to create employee lines for :count active employees.', ['count' => $employees['scoped_count']])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No employee lines')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Generate payroll to create employee lines for :count active employees.', ['count' => $employees['scoped_count']]))]); ?>
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
                </td>
            </tr>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\360\tabs\employees.blade.php ENDPATH**/ ?>