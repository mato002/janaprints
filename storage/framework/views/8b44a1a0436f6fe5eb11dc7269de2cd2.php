<?php
    $statusRaw = $overview['employment_status'] ?? ($employee->employment_status?->value ?? 'active');
    $canUpdate = auth()->user()->can('update', $employee);
    $canCompensation = auth()->user()->can('create', App\Models\Hr\EmployeeCompensation::class);
    $canLeave = auth()->user()->can('hr.leave.view');
    $canAttendance = auth()->user()->can('hr.attendance.view');
    $canDocuments = auth()->user()->can('hr.documents.view');
    $canAssets = auth()->user()->can('assets.view') || auth()->user()->can('assets.assign');
    $canExit = auth()->user()->can('hr.exit.manage') || auth()->user()->can('hr.exit.view');
?>

<nav class="employee-360__actions" aria-label="<?php echo e(__('Employee actions')); ?>">
    <div class="employee-360__actions-primary">
        <?php if($canUpdate): ?>
            <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" class="erp-btn-primary employee-360__action" data-erp-modal-open>
                <?php echo e(__('Edit')); ?>

            </a>
        <?php endif; ?>
        <?php if($canCompensation): ?>
            <a href="<?php echo e(route('admin.hr.compensation.edit', $employee)); ?>" class="erp-btn-secondary employee-360__action">
                <?php echo e(__('Payroll')); ?>

            </a>
        <?php endif; ?>
    </div>

    <div class="employee-360__actions-secondary">
        <?php if($canLeave): ?>
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('leave')"><?php echo e(__('Leave')); ?></button>
        <?php endif; ?>
        <?php if($canAttendance): ?>
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('attendance')"><?php echo e(__('Attendance')); ?></button>
        <?php endif; ?>
        <?php if($canDocuments): ?>
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('documents')"><?php echo e(__('Documents')); ?></button>
        <?php endif; ?>

        <details class="employee-360__more">
            <summary class="erp-btn-secondary employee-360__action employee-360__more-trigger"><?php echo e(__('More')); ?></summary>
            <div class="employee-360__more-menu" role="menu">
                <?php if($canAssets): ?>
                    <button type="button" class="employee-360__more-item" role="menuitem" @click="setTab('assets')"><?php echo e(__('Assign Asset')); ?></button>
                <?php endif; ?>
                <?php if($canUpdate): ?>
                    <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" class="employee-360__more-item" role="menuitem" data-erp-modal-open><?php echo e(__('Promote / Update role')); ?></a>
                <?php endif; ?>
                <button type="button" class="employee-360__more-item" role="menuitem" onclick="window.print()"><?php echo e(__('Print Profile')); ?></button>
                <?php if($overview['email'] ?? null): ?>
                    <a href="mailto:<?php echo e($overview['email']); ?>" class="employee-360__more-item" role="menuitem"><?php echo e(__('Email')); ?></a>
                <?php endif; ?>
                <?php if($overview['phone'] ?? null): ?>
                    <a href="sms:<?php echo e(preg_replace('/\s+/', '', (string) $overview['phone'])); ?>" class="employee-360__more-item" role="menuitem"><?php echo e(__('SMS')); ?></a>
                <?php endif; ?>
                <?php if($canExit && $statusRaw !== 'terminated'): ?>
                    <a href="<?php echo e(route('admin.hr.exit.create')); ?>" class="employee-360__more-item employee-360__more-item--danger" role="menuitem"><?php echo e(__('Terminate')); ?></a>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.employees.index')); ?>" class="employee-360__more-item" role="menuitem"><?php echo e(__('All employees')); ?></a>
            </div>
        </details>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\action-toolbar.blade.php ENDPATH**/ ?>