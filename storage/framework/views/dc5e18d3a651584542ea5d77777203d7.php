<?php if(! $employee): ?>
    <?php echo $__env->make('admin.hr.partials.employee-lookup-select', [
        'employees' => $employees,
        'selectClass' => 'erp-input w-full',
        'allowCreate' => false,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="erp-label"><?php echo e(__('Effective Date')); ?></label>
        <input type="date" name="effective_from" class="erp-input w-full" value="<?php echo e(old('effective_from', now()->toDateString())); ?>" required>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Basic Salary')); ?></label>
        <input type="number" step="0.01" min="0" name="basic_salary" class="erp-input w-full" value="<?php echo e(old('basic_salary', $compensation?->basic_salary ?? '')); ?>" required>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('House Allowance')); ?></label>
        <input type="number" step="0.01" min="0" name="house_allowance" class="erp-input w-full" value="<?php echo e(old('house_allowance', $compensation?->house_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Transport Allowance')); ?></label>
        <input type="number" step="0.01" min="0" name="transport_allowance" class="erp-input w-full" value="<?php echo e(old('transport_allowance', $compensation?->transport_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Medical Allowance')); ?></label>
        <input type="number" step="0.01" min="0" name="medical_allowance" class="erp-input w-full" value="<?php echo e(old('medical_allowance', $compensation?->medical_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Risk Allowance')); ?></label>
        <input type="number" step="0.01" min="0" name="risk_allowance" class="erp-input w-full" value="<?php echo e(old('risk_allowance', $compensation?->risk_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Responsibility Allowance')); ?></label>
        <input type="number" step="0.01" min="0" name="responsibility_allowance" class="erp-input w-full" value="<?php echo e(old('responsibility_allowance', $compensation?->responsibility_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Payment Frequency')); ?></label>
        <select name="payment_frequency" class="erp-input w-full" required>
            <?php $__currentLoopData = $paymentFrequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $freq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($freq->value); ?>" <?php if(old('payment_frequency', $compensation?->payment_frequency?->value ?? 'monthly') === $freq->value): echo 'selected'; endif; ?>><?php echo e($freq->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <?php echo $__env->make('admin.hr.compensation.partials.payroll-group-select', [
            'value' => old('payroll_group', $compensation?->payroll_group ?? 'main'),
            'groups' => $payrollGroups,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Currency')); ?></label>
        <input type="text" name="currency" maxlength="3" class="erp-input w-full" value="<?php echo e(old('currency', $compensation?->currency ?? 'KES')); ?>" required>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Payroll class')); ?></label>
        <select name="salary_template_id" class="erp-input w-full">
            <option value=""><?php echo e(__('None')); ?></option>
            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($template->id); ?>" <?php if((int) old('salary_template_id', $compensation?->salary_template_id) === $template->id): echo 'selected'; endif; ?>><?php echo e($template->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<?php if($employee): ?>
    <div>
        <label class="erp-label"><?php echo e(__('Change Reason')); ?></label>
        <textarea name="change_reason" class="erp-input w-full" rows="2"><?php echo e(old('change_reason')); ?></textarea>
    </div>
<?php endif; ?>

<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="require_approval" value="1" class="rounded border-slate-300">
    <?php echo e(__('Require approval before activation')); ?>

</label>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\compensation\partials\form-fields.blade.php ENDPATH**/ ?>