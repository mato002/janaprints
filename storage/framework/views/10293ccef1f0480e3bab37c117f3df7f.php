<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if($overview['is_suspended'] ?? false): ?>
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <?php echo e(__('This employee is suspended. ERP access is blocked and they are excluded from payroll runs.')); ?>

        </div>
    <?php elseif($overview['access_restricted'] ?? false): ?>
        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <?php echo e(__('ERP access is restricted for this employee.')); ?>

        </div>
    <?php endif; ?>

    <?php if(! ($overview['payroll_profile_complete'] ?? true)): ?>
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium"><?php echo e(__('Payroll profile incomplete')); ?></p>
            <p class="mt-1"><?php echo e(__('Missing: :fields', ['fields' => collect($overview['missing_payroll_fields'] ?? [])->pluck('label')->implode(', ')])); ?></p>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $employee)): ?>
                <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" class="mt-2 inline-flex text-sm font-semibold text-erp-accent hover:underline" data-erp-modal-open>
                    <?php echo e(__('Update employee profile')); ?>

                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h3 class="mb-3 text-sm font-semibold text-erp-primary"><?php echo e(__('Employment')); ?></h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Employee Number')); ?></dt><dd class="erp-ref-code font-medium"><?php echo e($overview['employee_number']); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Name')); ?></dt><dd class="font-medium"><?php echo e($overview['name']); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Department')); ?></dt><dd><?php echo e($overview['department'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($overview['branch'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Job Title')); ?></dt><dd><?php echo e($overview['job_title'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Supervisor')); ?></dt><dd><?php echo e($supervisor?->full_name ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Employment Status')); ?></dt><dd><?php echo e($overview['employment_status'] ? ucfirst(str_replace('_', ' ', $overview['employment_status'])) : '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Hire Date')); ?></dt><dd><?php echo e($overview['hire_date']?->format('M j, Y') ?? '—'); ?></dd></div>
    </dl>

    <h3 class="mb-3 mt-6 text-sm font-semibold text-erp-primary"><?php echo e(__('Personal & contact')); ?></h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Gender')); ?></dt><dd><?php echo e($overview['gender'] ? ucfirst($overview['gender']) : '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Date of birth')); ?></dt><dd><?php echo e($overview['date_of_birth']?->format('M j, Y') ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('National ID')); ?></dt><dd><?php echo e($overview['national_id'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Personal email')); ?></dt><dd><?php echo e($overview['email'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Phone')); ?></dt><dd><?php echo e($overview['phone'] ?? '—'); ?></dd></div>
        <div class="sm:col-span-2 lg:col-span-3"><dt class="text-xs text-slate-500"><?php echo e(__('Address')); ?></dt><dd><?php echo e($overview['address'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Emergency contact')); ?></dt><dd><?php echo e($overview['emergency_contact_name'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Emergency phone')); ?></dt><dd><?php echo e($overview['emergency_contact_phone'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Next of kin')); ?></dt><dd><?php echo e($overview['next_of_kin_name'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Next of kin phone')); ?></dt><dd><?php echo e($overview['next_of_kin_phone'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Relationship')); ?></dt><dd><?php echo e($overview['next_of_kin_relationship'] ?? '—'); ?></dd></div>
    </dl>

    <h3 class="mb-3 mt-6 text-sm font-semibold text-erp-primary"><?php echo e(__('Statutory & payroll payment')); ?></h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500"><?php echo e(__('KRA PIN')); ?></dt><dd><?php echo e($overview['kra_pin'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('NSSF number')); ?></dt><dd><?php echo e($overview['nssf_number'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('SHIF / NHIF number')); ?></dt><dd><?php echo e($overview['nhif_number'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Bank name')); ?></dt><dd><?php echo e($overview['bank_name'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Bank account')); ?></dt><dd><?php echo e($overview['bank_account_number'] ?? '—'); ?></dd></div>
        <div><dt class="text-xs text-slate-500"><?php echo e(__('Branch code')); ?></dt><dd><?php echo e($overview['bank_branch_code'] ?? '—'); ?></dd></div>
    </dl>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/employees/360/tabs/overview.blade.php ENDPATH**/ ?>