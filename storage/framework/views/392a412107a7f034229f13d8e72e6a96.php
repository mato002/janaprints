<?php
    $statusRaw = $overview['employment_status'] ?? ($employee->employment_status?->value ?? 'active');
    $statusLabel = ucfirst(str_replace('_', ' ', (string) $statusRaw));
    $statusTone = match ($statusRaw) {
        'active' => 'success',
        'on_leave' => 'info',
        'suspended' => 'warning',
        'terminated' => 'danger',
        default => 'neutral',
    };
    $photoUrl = $employee->photo ? asset('storage/'.$employee->photo) : null;
    $initials = collect(explode(' ', (string) $employee->full_name))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $payrollReady = (bool) ($overview['payroll_profile_complete'] ?? true);
    $jobTitle = $overview['job_title'] ?? null;
    $department = $overview['department'] ?? null;
    $branch = $overview['branch'] ?? null;
    $shiftName = $employee->shift?->name;
?>

<header class="employee-360__hero">
    <div class="employee-360__hero-main">
        <div class="employee-360__identity">
            <a href="<?php echo e(route('admin.employees.index')); ?>" class="employee-360__back" data-turbo-frame="erp-main">
                ← <?php echo e(__('Employees')); ?>

            </a>

            <div class="employee-360__identity-row">
                <div class="employee-360__avatar" aria-hidden="true">
                    <?php if($photoUrl): ?>
                        <img src="<?php echo e($photoUrl); ?>" alt="" class="employee-360__avatar-img">
                    <?php else: ?>
                        <span class="employee-360__avatar-initials"><?php echo e($initials ?: '?'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="employee-360__identity-text">
                    <h1 class="employee-360__name"><?php echo e($employee->full_name); ?></h1>
                    <p class="employee-360__meta">
                        <span class="employee-360__emp-no"><?php echo e($overview['employee_number']); ?></span>
                        <?php if($jobTitle): ?>
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span><?php echo e($jobTitle); ?></span>
                        <?php endif; ?>
                        <?php if($department): ?>
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span><?php echo e($department); ?></span>
                        <?php endif; ?>
                        <?php if($branch): ?>
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span><?php echo e($branch); ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="employee-360__submeta">
                        <span class="employee-360__status employee-360__status--<?php echo e($statusTone); ?>"><?php echo e($statusLabel); ?></span>
                        <?php if($overview['hire_date']): ?>
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span><?php echo e(__('Hired')); ?> <?php echo e($overview['hire_date']->format('d M Y')); ?></span>
                        <?php endif; ?>
                        <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                        <?php if($supervisor): ?>
                            <span><?php echo e(__('Reports to')); ?> <?php echo e($supervisor->full_name); ?></span>
                        <?php else: ?>
                            <span class="employee-360__empty-inline"><?php echo e(__('No supervisor assigned')); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php echo $__env->make('admin.hr.employees.360.action-toolbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="employee-360__ribbon" aria-label="<?php echo e(__('Employee context')); ?>">
        <span class="employee-360__badge employee-360__badge--<?php echo e($statusTone); ?>"><?php echo e($statusLabel); ?></span>
        <?php if($department): ?>
            <span class="employee-360__badge employee-360__badge--dept"><?php echo e($department); ?></span>
        <?php endif; ?>
        <?php if($jobTitle): ?>
            <span class="employee-360__badge employee-360__badge--role"><?php echo e($jobTitle); ?></span>
        <?php endif; ?>
        <?php if($branch): ?>
            <span class="employee-360__badge employee-360__badge--branch"><?php echo e($branch); ?></span>
        <?php endif; ?>
        <?php if($shiftName): ?>
            <span class="employee-360__badge employee-360__badge--info"><?php echo e($shiftName); ?></span>
        <?php endif; ?>
        <?php if($payrollReady): ?>
            <span class="employee-360__badge employee-360__badge--ready"><?php echo e(__('Payroll Ready')); ?></span>
        <?php else: ?>
            <span class="employee-360__badge employee-360__badge--incomplete"><?php echo e(__('Payroll Incomplete')); ?></span>
        <?php endif; ?>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\header.blade.php ENDPATH**/ ?>