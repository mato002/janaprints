<?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $employee->full_name,'description' => $employee->employee_number.' · '.$employee->branch?->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->full_name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->employee_number.' · '.$employee->branch?->name)]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('admin.employees.index')); ?>" class="erp-btn-secondary"><?php echo e(__('All employees')); ?></a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $employee)): ?>
            <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" class="erp-btn-secondary" data-erp-modal-open><?php echo e(__('Edit profile')); ?></a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Hr\EmployeeCompensation::class)): ?>
            <a href="<?php echo e(route('admin.hr.compensation.edit', $employee)); ?>" class="erp-btn-primary"><?php echo e(__('Manage compensation')); ?></a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/employees/360/header.blade.php ENDPATH**/ ?>