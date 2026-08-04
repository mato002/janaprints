<?php
    $workflow = old('approval_workflow', $documentType->workflow_json['approval_workflow'] ?? '') ?? '';
    $notificationWorkflow = old('notification_workflow', $documentType->workflow_json['notification_workflow'] ?? '') ?? '';
    $auditTracking = old('audit_tracking', $documentType->workflow_json['audit_tracking'] ?? true);
    $archivalRules = old('archival_rules', $documentType->workflow_json['archival_rules'] ?? '') ?? '';
    $printTemplate = old('print_template', $documentType->workflow_json['print_template'] ?? '') ?? '';
    $isSystem = $documentType->is_system ?? false;
?>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Document Configuration')); ?></h3>

        <div>
            <?php if($isSystem): ?>
                <label class="erp-label" for="code"><?php echo e(__('Document Code')); ?></label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    value="<?php echo e(old('code', $documentType->code ?? '')); ?>"
                    class="erp-input w-full"
                    disabled
                >
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $documentType->exists ? $documentType : null,'erp' => true,'maxlength' => '50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentType->exists ? $documentType : null),'erp' => true,'maxlength' => '50']); ?>
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
            <?php endif; ?>
            <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label class="erp-label" for="name"><?php echo e(__('Document Name')); ?></label>
            <input type="text" id="name" name="name" value="<?php echo e(old('name', $documentType->name ?? '')); ?>" class="erp-input w-full" required>
        </div>

        <div>
            <label class="erp-label" for="module"><?php echo e(__('Module')); ?></label>
            <select id="module" name="module" class="erp-input w-full" required>
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(old('module', $documentType->module?->value ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <label class="erp-label" for="prefix"><?php echo e(__('Prefix')); ?></label>
            <input type="text" id="prefix" name="prefix" value="<?php echo e(old('prefix', $documentType->prefix ?? '')); ?>" class="erp-input w-full" maxlength="20">
        </div>

        <div>
            <label class="erp-label" for="number_series_key"><?php echo e(__('Number Series')); ?></label>
            <select id="number_series_key" name="number_series_key" class="erp-input w-full">
                <option value=""><?php echo e(__('Not linked')); ?></option>
                <?php $__currentLoopData = $numberSeriesOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(old('number_series_key', $documentType->number_series_key ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="auto_numbering" value="0">
            <input type="checkbox" id="auto_numbering" name="auto_numbering" value="1" class="rounded border-erp-border text-erp-accent" <?php if(old('auto_numbering', $documentType->auto_numbering ?? true)): echo 'checked'; endif; ?>>
            <label for="auto_numbering" class="text-sm text-slate-700"><?php echo e(__('Auto Numbering')); ?></label>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Governance Settings')); ?></h3>

        <div class="flex items-center gap-2">
            <input type="hidden" name="approval_required" value="0">
            <input type="checkbox" id="approval_required" name="approval_required" value="1" class="rounded border-erp-border text-erp-accent" <?php if(old('approval_required', $documentType->approval_required ?? false)): echo 'checked'; endif; ?>>
            <label for="approval_required" class="text-sm text-slate-700"><?php echo e(__('Approval Required')); ?></label>
        </div>

        <div>
            <label class="erp-label" for="approval_levels"><?php echo e(__('Approval Levels')); ?></label>
            <input type="number" id="approval_levels" name="approval_levels" value="<?php echo e(old('approval_levels', $documentType->approval_levels ?? 0)); ?>" min="0" max="10" class="erp-input w-24">
        </div>

        <div>
            <label class="erp-label" for="approval_rule_type"><?php echo e(__('Approval Rule Link')); ?></label>
            <select id="approval_rule_type" name="approval_rule_type" class="erp-input w-full">
                <option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $approvalRuleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(old('approval_rule_type', $documentType->approval_rule_type ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <label class="erp-label" for="retention_period_days"><?php echo e(__('Retention Period (days)')); ?></label>
            <input type="number" id="retention_period_days" name="retention_period_days" value="<?php echo e(old('retention_period_days', $documentType->retention_period_days ?? '')); ?>" min="1" class="erp-input w-full">
        </div>

        <div>
            <label class="erp-label" for="form_key"><?php echo e(__('Form Controls Link')); ?></label>
            <select id="form_key" name="form_key" class="erp-input w-full">
                <option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $formOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(old('form_key', $documentType->form_key ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
</div>

<div class="mt-8 space-y-4">
    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Workflow Settings')); ?></h3>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="erp-label" for="approval_workflow"><?php echo e(__('Approval Workflow')); ?></label>
            <input type="text" id="approval_workflow" name="approval_workflow" value="<?php echo e($workflow); ?>" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="notification_workflow"><?php echo e(__('Notification Workflow')); ?></label>
            <input type="text" id="notification_workflow" name="notification_workflow" value="<?php echo e($notificationWorkflow); ?>" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="archival_rules"><?php echo e(__('Archival Rules')); ?></label>
            <input type="text" id="archival_rules" name="archival_rules" value="<?php echo e($archivalRules); ?>" class="erp-input w-full">
        </div>
        <div>
            <label class="erp-label" for="print_template"><?php echo e(__('Print Template')); ?></label>
            <input type="text" id="print_template" name="print_template" value="<?php echo e($printTemplate); ?>" class="erp-input w-full">
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="audit_tracking" value="0">
        <input type="checkbox" id="audit_tracking" name="audit_tracking" value="1" class="rounded border-erp-border text-erp-accent" <?php if($auditTracking): echo 'checked'; endif; ?>>
        <label for="audit_tracking" class="text-sm text-slate-700"><?php echo e(__('Audit Tracking')); ?></label>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\document-types\partials\form.blade.php ENDPATH**/ ?>