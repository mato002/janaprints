<?php
    $payrollFieldsTotal = 5;
    $missingPayroll = collect($overview['missing_payroll_fields'] ?? []);
    $missingRecommended = collect($overview['missing_recommended_fields'] ?? []);
    $payrollReady = (bool) ($overview['payroll_profile_complete'] ?? true);
    $payrollPct = (int) round((($payrollFieldsTotal - $missingPayroll->count()) / max($payrollFieldsTotal, 1)) * 100);

    $leaveBalanceDays = collect($leave['balances'] ?? [])->sum(fn ($b) => (float) ($b['available'] ?? 0));
    $pendingLeave = $leave['pending']->count();
    $todayAttendance = null;
    foreach ($attendance['records'] as $record) {
        $date = $record->attendance_date ?? null;
        if ($date && \Illuminate\Support\Carbon::parse($date)->isToday()) {
            $todayAttendance = $record;
            break;
        }
    }
    $nextPayslip = $payroll['payslips']->first();
    $birthdaySoon = false;
    $birthdayLabel = null;
    if ($overview['date_of_birth']) {
        $nextBirthday = $overview['date_of_birth']->copy()->year((int) now()->year)->startOfDay();
        if ($nextBirthday->lt(now()->startOfDay())) {
            $nextBirthday->addYear();
        }
        $daysUntil = (int) now()->startOfDay()->diffInDays($nextBirthday, false);
        if ($daysUntil >= 0 && $daysUntil <= 30) {
            $birthdaySoon = true;
            $birthdayLabel = $daysUntil === 0
                ? __('Today')
                : __('In :days days', ['days' => $daysUntil]);
        }
    }
    $editUrl = route('admin.employees.edit', $employee);
    $canUpdate = auth()->user()->can('update', $employee);
    $hasOpenTasks = $pendingLeave > 0 || $missingPayroll->isNotEmpty() || $missingRecommended->isNotEmpty();
    $hasPendingDocs = $documents['all']->total() === 0 || $missingRecommended->isNotEmpty();
    $showIntel = $todayAttendance
        || $leaveBalanceDays > 0
        || $pendingLeave > 0
        || $nextPayslip
        || $hasOpenTasks
        || $hasPendingDocs
        || $assets['issued']->isNotEmpty()
        || $birthdaySoon
        || $timeline->isNotEmpty();

    $empty = function (?string $value, string $message) use ($canUpdate, $editUrl): array {
        $filled = filled($value);

        return [
            'filled' => $filled,
            'display' => $filled ? $value : $message,
            'empty' => ! $filled,
            'edit' => $canUpdate && ! $filled,
            'url' => $editUrl,
        ];
    };
?>

<div class="employee-360__overview">
    <div class="employee-360__overview-main">
        <?php if($overview['is_suspended'] ?? false): ?>
            <div class="employee-360__alert employee-360__alert--warning">
                <?php echo e(__('This employee is suspended. ERP access is blocked and they are excluded from payroll runs.')); ?>

            </div>
        <?php elseif($overview['access_restricted'] ?? false): ?>
            <div class="employee-360__alert employee-360__alert--neutral">
                <?php echo e(__('ERP access is restricted for this employee.')); ?>

            </div>
        <?php endif; ?>

        <section class="employee-360__card employee-360__card--readiness <?php echo e($payrollReady ? 'employee-360__card--ready' : 'employee-360__card--attention'); ?>">
            <div class="employee-360__card-head">
                <div class="employee-360__card-title-wrap">
                    <span class="employee-360__card-icon employee-360__card-icon--payroll" aria-hidden="true">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'cash','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cash','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    </span>
                    <h2 class="employee-360__card-title"><?php echo e(__('Payroll Readiness')); ?></h2>
                </div>
                <span class="employee-360__readiness-pct"><?php echo e($payrollPct); ?>%</span>
            </div>

            <div class="employee-360__progress" role="progressbar" aria-valuenow="<?php echo e($payrollPct); ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="employee-360__progress-fill <?php echo e($payrollReady ? 'is-ready' : 'is-incomplete'); ?>" style="width: <?php echo e($payrollPct); ?>%"></div>
            </div>

            <?php if($payrollReady): ?>
                <p class="employee-360__readiness-ok"><?php echo e(__('All required statutory and bank details are complete.')); ?></p>
            <?php else: ?>
                <p class="employee-360__readiness-missing-label"><?php echo e(__('Missing')); ?></p>
                <ul class="employee-360__missing-list">
                    <?php $__currentLoopData = $missingPayroll; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if($canUpdate): ?>
                                <a href="<?php echo e($editUrl); ?>" class="employee-360__missing-link" data-erp-modal-open><?php echo e($field['label']); ?></a>
                            <?php else: ?>
                                <?php echo e($field['label']); ?>

                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </section>

        <div class="employee-360__card-grid">
            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--employment" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'identification','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'identification','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Employment')); ?></h2>
                    </div>
                    <?php if($canUpdate): ?>
                        <a href="<?php echo e($editUrl); ?>" class="employee-360__card-action" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
                    <?php endif; ?>
                </div>
                <div class="employee-360__fields">
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'badge-check','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'badge-check','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Employee No.')); ?></span>
                        <span class="employee-360__field-value erp-ref-code"><?php echo e($overview['employee_number']); ?></span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'office-building','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'office-building','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Department')); ?></span>
                        <?php $f = $empty($overview['department'] ?? null, __('No department assigned')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'tag','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tag','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Position')); ?></span>
                        <?php $f = $empty($overview['job_title'] ?? null, __('No position assigned')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'building','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'building','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Branch')); ?></span>
                        <?php $f = $empty($overview['branch'] ?? null, __('No branch assigned')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>"><?php echo e($f['display']); ?></span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'users','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Supervisor')); ?></span>
                        <?php $f = $empty($supervisor?->full_name, __('No supervisor assigned')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>"><?php echo e($f['display']); ?></span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'calendar','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Hire Date')); ?></span>
                        <span class="employee-360__field-value">
                            <?php echo e($overview['hire_date']?->format('d M Y') ?? __('No hire date')); ?>

                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'shield-check','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Status')); ?></span>
                        <span class="employee-360__field-value">
                            <?php echo e($overview['employment_status'] ? ucfirst(str_replace('_', ' ', $overview['employment_status'])) : __('Unknown')); ?>

                        </span>
                    </div>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--personal" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'user-circle','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-circle','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Personal')); ?></h2>
                    </div>
                    <?php if($canUpdate): ?>
                        <a href="<?php echo e($editUrl); ?>" class="employee-360__card-action" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
                    <?php endif; ?>
                </div>
                <div class="employee-360__fields">
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php echo e(__('Gender')); ?></span>
                        <span class="employee-360__field-value <?php echo e(blank($overview['gender']) ? 'is-empty' : ''); ?>">
                            <?php echo e($overview['gender'] ? ucfirst($overview['gender']) : __('Not specified')); ?>

                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'calendar','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Date of birth')); ?></span>
                        <?php $f = $empty($overview['date_of_birth']?->format('d M Y'), __('No date of birth')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'identification','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'identification','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('National ID')); ?></span>
                        <?php $f = $empty($overview['national_id'] ?? null, __('No national ID added')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php echo e(__('Personal email')); ?></span>
                        <?php $f = $empty($overview['email'] ?? null, __('No email added')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>"><?php echo e($f['display']); ?></span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'device-mobile','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'device-mobile','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Phone')); ?></span>
                        <?php $f = $empty($overview['phone'] ?? null, __('No phone added')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                    <div class="employee-360__field employee-360__field--wide">
                        <span class="employee-360__field-label"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'location-marker','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location-marker','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?> <?php echo e(__('Address')); ?></span>
                        <?php $f = $empty($overview['address'] ?? null, __('No address added')); ?>
                        <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                            <?php echo e($f['display']); ?>

                            <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                        </span>
                    </div>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--statutory" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'receipt-tax','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'receipt-tax','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Statutory & Bank')); ?></h2>
                    </div>
                    <?php if($canUpdate): ?>
                        <a href="<?php echo e($editUrl); ?>" class="employee-360__card-action" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
                    <?php endif; ?>
                </div>
                <div class="employee-360__fields">
                    <?php $__currentLoopData = [
                        ['label' => __('KRA PIN'), 'value' => $overview['kra_pin'] ?? null, 'empty' => __('No KRA PIN added')],
                        ['label' => __('NSSF'), 'value' => $overview['nssf_number'] ?? null, 'empty' => __('No NSSF number added')],
                        ['label' => __('SHIF / NHIF'), 'value' => $overview['nhif_number'] ?? null, 'empty' => __('No SHIF/NHIF number added')],
                        ['label' => __('Bank name'), 'value' => $overview['bank_name'] ?? null, 'empty' => __('No bank account added')],
                        ['label' => __('Bank account'), 'value' => $overview['bank_account_number'] ?? null, 'empty' => __('No bank account added')],
                        ['label' => __('Branch code'), 'value' => $overview['bank_branch_code'] ?? null, 'empty' => __('No branch code added')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $f = $empty($row['value'], $row['empty']); ?>
                        <div class="employee-360__field">
                            <span class="employee-360__field-label"><?php echo e($row['label']); ?></span>
                            <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                                <?php echo e($f['display']); ?>

                                <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--emergency" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Emergency & Contacts')); ?></h2>
                    </div>
                    <?php if($canUpdate): ?>
                        <a href="<?php echo e($editUrl); ?>" class="employee-360__card-action" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
                    <?php endif; ?>
                </div>
                <div class="employee-360__fields">
                    <?php $__currentLoopData = [
                        ['label' => __('Emergency contact'), 'value' => $overview['emergency_contact_name'] ?? null, 'empty' => __('No emergency contact')],
                        ['label' => __('Emergency phone'), 'value' => $overview['emergency_contact_phone'] ?? null, 'empty' => __('No emergency phone')],
                        ['label' => __('Next of kin'), 'value' => $overview['next_of_kin_name'] ?? null, 'empty' => __('No next of kin')],
                        ['label' => __('Next of kin phone'), 'value' => $overview['next_of_kin_phone'] ?? null, 'empty' => __('No next of kin phone')],
                        ['label' => __('Relationship'), 'value' => $overview['next_of_kin_relationship'] ?? null, 'empty' => __('No relationship set')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $f = $empty($row['value'], $row['empty']); ?>
                        <div class="employee-360__field">
                            <span class="employee-360__field-label"><?php echo e($row['label']); ?></span>
                            <span class="employee-360__field-value <?php echo e($f['empty'] ? 'is-empty' : ''); ?>">
                                <?php echo e($f['display']); ?>

                                <?php if($f['edit']): ?> <a href="<?php echo e($f['url']); ?>" class="employee-360__inline-link" data-erp-modal-open><?php echo e(__('Add')); ?></a> <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--assets" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'cube','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Assets')); ?></h2>
                    </div>
                    <button type="button" class="employee-360__card-action" @click="setTab('assets')"><?php echo e(__('View')); ?></button>
                </div>
                <?php if($assets['issued']->isNotEmpty()): ?>
                    <ul class="employee-360__compact-list">
                        <?php $__currentLoopData = $assets['issued']->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <span class="employee-360__compact-title"><?php echo e($asset->asset_name ?? $asset->asset_number); ?></span>
                                <span class="employee-360__compact-meta"><?php echo e($asset->asset_number); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <?php if($assets['issued']->count() > 4): ?>
                        <p class="employee-360__more-count"><?php echo e(__('+:count more', ['count' => $assets['issued']->count() - 4])); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="employee-360__empty-block"><?php echo e(__('No assets issued')); ?></p>
                <?php endif; ?>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--docs" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'document-text','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document-text','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                        </span>
                        <h2 class="employee-360__card-title"><?php echo e(__('Documents')); ?></h2>
                    </div>
                    <button type="button" class="employee-360__card-action" @click="setTab('documents')"><?php echo e(__('View')); ?></button>
                </div>
                <div class="employee-360__stat-row">
                    <div>
                        <span class="employee-360__stat-value"><?php echo e($documents['all']->total()); ?></span>
                        <span class="employee-360__stat-label"><?php echo e(__('On file')); ?></span>
                    </div>
                    <div>
                        <span class="employee-360__stat-value"><?php echo e($missingRecommended->count()); ?></span>
                        <span class="employee-360__stat-label"><?php echo e(__('Profile gaps')); ?></span>
                    </div>
                </div>
                <?php if($missingRecommended->isNotEmpty()): ?>
                    <ul class="employee-360__missing-list employee-360__missing-list--compact">
                        <?php $__currentLoopData = $missingRecommended->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <?php if($canUpdate): ?>
                                    <a href="<?php echo e($editUrl); ?>" class="employee-360__missing-link" data-erp-modal-open><?php echo e($field['label']); ?></a>
                                <?php else: ?>
                                    <?php echo e($field['label']); ?>

                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php else: ?>
                    <p class="employee-360__readiness-ok"><?php echo e(__('Recommended profile fields are complete.')); ?></p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php if($showIntel): ?>
        <aside class="employee-360__intel" aria-label="<?php echo e(__('Employee intelligence')); ?>">
            <h2 class="employee-360__intel-heading"><?php echo e(__('Intelligence')); ?></h2>

            <?php if($todayAttendance): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__("Today's Attendance")); ?></h3>
                    <p class="employee-360__widget-value">
                        <?php echo e($todayAttendance->status instanceof \App\Enums\AttendanceStatus ? $todayAttendance->status->label() : ucfirst((string) $todayAttendance->status)); ?>

                    </p>
                    <?php if($todayAttendance->clock_in_at): ?>
                        <p class="employee-360__widget-meta"><?php echo e(__('In')); ?> <?php echo e($todayAttendance->clock_in_at->format('H:i')); ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if($leaveBalanceDays > 0 || $pendingLeave > 0): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Leave Balance')); ?></h3>
                    <p class="employee-360__widget-value"><?php echo e(number_format($leaveBalanceDays, 1)); ?> <?php echo e(__('days')); ?></p>
                    <?php if($pendingLeave > 0): ?>
                        <p class="employee-360__widget-meta employee-360__widget-meta--warn">
                            <?php echo e(__(':count pending request(s)', ['count' => $pendingLeave])); ?>

                        </p>
                    <?php endif; ?>
                    <button type="button" class="employee-360__widget-link" @click="setTab('leave')"><?php echo e(__('Open leave')); ?></button>
                </section>
            <?php endif; ?>

            <?php if($nextPayslip): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Latest Payroll')); ?></h3>
                    <p class="employee-360__widget-value"><?php echo e(number_format((float) $nextPayslip->net_pay, 0)); ?></p>
                    <p class="employee-360__widget-meta">
                        <?php echo e($nextPayslip->payrollRun?->period_end?->format('M Y') ?? $nextPayslip->created_at?->format('M Y')); ?>

                    </p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('payroll')"><?php echo e(__('View payslips')); ?></button>
                </section>
            <?php endif; ?>

            <?php if($hasOpenTasks): ?>
                <section class="employee-360__widget employee-360__widget--tasks">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Open Tasks')); ?></h3>
                    <ul class="employee-360__task-list">
                        <?php if($missingPayroll->isNotEmpty()): ?>
                            <li><?php echo e(__('Complete payroll profile (:count)', ['count' => $missingPayroll->count()])); ?></li>
                        <?php endif; ?>
                        <?php if($pendingLeave > 0): ?>
                            <li><?php echo e(__('Review :count leave request(s)', ['count' => $pendingLeave])); ?></li>
                        <?php endif; ?>
                        <?php if($missingRecommended->isNotEmpty()): ?>
                            <li><?php echo e(__('Fill :count recommended fields', ['count' => $missingRecommended->count()])); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if($hasPendingDocs): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Pending Documents')); ?></h3>
                    <p class="employee-360__widget-value"><?php echo e($documents['all']->total()); ?> <?php echo e(__('on file')); ?></p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('documents')"><?php echo e(__('Manage documents')); ?></button>
                </section>
            <?php endif; ?>

            <?php if($assets['issued']->isNotEmpty()): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Assets')); ?></h3>
                    <p class="employee-360__widget-value"><?php echo e($assets['issued']->count()); ?> <?php echo e(__('issued')); ?></p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('assets')"><?php echo e(__('View assets')); ?></button>
                </section>
            <?php endif; ?>

            <?php if($birthdaySoon): ?>
                <section class="employee-360__widget employee-360__widget--celebrate">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Upcoming Birthday')); ?></h3>
                    <p class="employee-360__widget-value"><?php echo e($birthdayLabel); ?></p>
                    <p class="employee-360__widget-meta"><?php echo e($overview['date_of_birth']->format('d M')); ?></p>
                </section>
            <?php endif; ?>

            <?php if($timeline->isNotEmpty()): ?>
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title"><?php echo e(__('Recent Activity')); ?></h3>
                    <ul class="employee-360__timeline-mini">
                        <?php $__currentLoopData = $timeline->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <span class="employee-360__timeline-mini-title"><?php echo e($event->title); ?></span>
                                <span class="employee-360__timeline-mini-date"><?php echo e($event->eventDatetime->format('d M')); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="employee-360__widget-link" @click="setTab('timeline')"><?php echo e(__('Full timeline')); ?></button>
                </section>
            <?php endif; ?>
        </aside>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\tabs\overview.blade.php ENDPATH**/ ?>