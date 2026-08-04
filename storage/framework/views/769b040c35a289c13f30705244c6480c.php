<?php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
    $isEdit = $isEdit ?? false;
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $isEdit ? __('Edit Escalation Rule') : __('Create Escalation Rule'),'breadcrumbs' => [
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $hubBackUrl],
        ['label' => __('Escalations'), 'url' => route('admin.governance.escalations.index', $scopeQuery)],
        ['label' => $isEdit ? __('Edit') : __('Create')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.settings.partials.hub-toolbar', [
        'title' => $isEdit ? __('Edit Escalation Rule') : __('Create Escalation Rule'),
        'description' => __('Configure waiting periods and escalation routing for approval workflows.'),
        'backUrl' => route('admin.governance.escalations.index', $scopeQuery),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
        <form
            method="POST"
            action="<?php echo e($isEdit ? route('admin.governance.escalations.update', ['escalation' => $rule->id]) : route('admin.governance.escalations.store')); ?>"
            class="space-y-6"
        >
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
            <?php if($branchId): ?>
                <input type="hidden" name="branch_id" value="<?php echo e($branchId); ?>">
            <?php endif; ?>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="erp-label" for="name"><?php echo e(__('Rule Name')); ?></label>
                        <input type="text" id="name" name="name" value="<?php echo e(old('name', $rule->name ?? '')); ?>" class="erp-input w-full" required>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="erp-label" for="workflow_key"><?php echo e(__('Workflow')); ?></label>
                        <select id="workflow_key" name="workflow_key" class="erp-input w-full" required>
                            <option value=""><?php echo e(__('Select workflow')); ?></option>
                            <?php $__currentLoopData = $workflows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(old('workflow_key', $rule->workflow_key ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['workflow_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="erp-label" for="waiting_hours"><?php echo e(__('Waiting Period')); ?></label>
                        <select id="waiting_hours" name="waiting_hours" class="erp-input w-full" required>
                            <option value=""><?php echo e(__('Select waiting period')); ?></option>
                            <?php $__currentLoopData = $waitingPeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hours => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($hours); ?>" <?php if((int) old('waiting_hours', $rule->waiting_hours ?? 0) === (int) $hours): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['waiting_hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="erp-label" for="escalation_target_role"><?php echo e(__('Escalation Target')); ?></label>
                        <select id="escalation_target_role" name="escalation_target_role" class="erp-input w-full" required>
                            <option value=""><?php echo e(__('Select escalation target role')); ?></option>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($roleName); ?>" <?php if(old('escalation_target_role', $rule->escalation_target_role ?? '') === $roleName): echo 'selected'; endif; ?>><?php echo e($roleName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['escalation_target_role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="erp-label"><?php echo e(__('Escalation Method')); ?></label>
                        <div class="mt-2 space-y-2">
                            <?php $__currentLoopData = $escalationMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        name="escalation_method"
                                        value="<?php echo e($value); ?>"
                                        class="border-erp-border text-erp-accent"
                                        <?php if(old('escalation_method', $rule?->escalation_method?->value ?? 'reminder') === $value): echo 'checked'; endif; ?>
                                        required
                                    >
                                    <span><?php echo e($label); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            <?php echo e(__('Reminder sends a notification when the waiting period expires. Auto Escalate reassigns approval to the escalation target.')); ?>

                        </p>
                        <?php $__errorArgs = ['escalation_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="erp-label" for="description"><?php echo e(__('Description')); ?></label>
                        <textarea id="description" name="description" rows="3" class="erp-input w-full"><?php echo e(old('description', $rule->description ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-erp-border pt-4">
                <button type="submit" class="erp-btn-primary"><?php echo e($isEdit ? __('Save Changes') : __('Create Rule')); ?></button>
                <a href="<?php echo e(route('admin.governance.escalations.index', $scopeQuery)); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
            </div>
        </form>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\governance\escalations\form.blade.php ENDPATH**/ ?>