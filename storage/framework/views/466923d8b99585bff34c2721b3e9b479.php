<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $template,'erp' => true,'maxlength' => '30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($template),'erp' => true,'maxlength' => '30']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $attributes = $__attributesOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__attributesOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $component = $__componentOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__componentOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
    </div>
    <div>
        <label class="erp-label" for="template-name"><?php echo e(__('Name')); ?></label>
        <input type="text" id="template-name" name="name" class="erp-input w-full" value="<?php echo e(old('name', $template?->name)); ?>" required>
    </div>
    <div>
        <label class="erp-label" for="template-basic-salary"><?php echo e(__('Basic salary')); ?></label>
        <input type="number" step="0.01" min="0" id="template-basic-salary" name="basic_salary" class="erp-input w-full" value="<?php echo e(old('basic_salary', $template?->basic_salary)); ?>" required>
    </div>
    <div>
        <label class="erp-label" for="template-house"><?php echo e(__('House allowance')); ?></label>
        <input type="number" step="0.01" min="0" id="template-house" name="house_allowance" class="erp-input w-full" value="<?php echo e(old('house_allowance', $template?->house_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label" for="template-transport"><?php echo e(__('Transport allowance')); ?></label>
        <input type="number" step="0.01" min="0" id="template-transport" name="transport_allowance" class="erp-input w-full" value="<?php echo e(old('transport_allowance', $template?->transport_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label" for="template-medical"><?php echo e(__('Medical allowance')); ?></label>
        <input type="number" step="0.01" min="0" id="template-medical" name="medical_allowance" class="erp-input w-full" value="<?php echo e(old('medical_allowance', $template?->medical_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label" for="template-risk"><?php echo e(__('Risk allowance')); ?></label>
        <input type="number" step="0.01" min="0" id="template-risk" name="risk_allowance" class="erp-input w-full" value="<?php echo e(old('risk_allowance', $template?->risk_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label" for="template-responsibility"><?php echo e(__('Responsibility allowance')); ?></label>
        <input type="number" step="0.01" min="0" id="template-responsibility" name="responsibility_allowance" class="erp-input w-full" value="<?php echo e(old('responsibility_allowance', $template?->responsibility_allowance ?? 0)); ?>">
    </div>
    <div>
        <label class="erp-label" for="template-payment-frequency"><?php echo e(__('Payment frequency')); ?></label>
        <select id="template-payment-frequency" name="payment_frequency" class="erp-input w-full" required>
            <?php $__currentLoopData = $paymentFrequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $frequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($frequency->value); ?>" <?php if(old('payment_frequency', $template?->payment_frequency?->value ?? 'monthly') === $frequency->value): echo 'selected'; endif; ?>><?php echo e($frequency->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <?php echo $__env->make('admin.hr.compensation.partials.payroll-group-select', [
            'value' => old('payroll_group', $template?->payroll_group ?? 'main'),
            'groups' => $payrollGroups,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div>
        <label class="erp-label" for="template-currency"><?php echo e(__('Currency')); ?></label>
        <input type="text" id="template-currency" name="currency" maxlength="3" class="erp-input w-full" value="<?php echo e(old('currency', $template?->currency ?? 'KES')); ?>" required>
    </div>
</div>

<?php if($template): ?>
    <label class="mt-4 flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" <?php if(old('is_active', $template->is_active)): echo 'checked'; endif; ?>>
        <?php echo e(__('Active — available for new employee assignments')); ?>

    </label>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\compensation\templates\partials\form-fields.blade.php ENDPATH**/ ?>