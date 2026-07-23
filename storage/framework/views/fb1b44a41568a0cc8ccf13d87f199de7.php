<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['row', 'canManage', 'roles', 'permissions']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['row', 'canManage', 'roles', 'permissions']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => '!p-0 overflow-hidden','id' => 'approval-panel-'.e($row['rule_type']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 overflow-hidden','id' => 'approval-panel-'.e($row['rule_type']).'']); ?>
    <div class="border-b border-erp-border px-5 py-4 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    <?php echo e(__('Approvals')); ?>

                    <span class="mx-1 text-slate-300">/</span>
                    <span class="text-erp-accent"><?php echo e($row['label']); ?></span>
                </p>
                <h2 class="mt-1 text-lg font-semibold text-erp-primary"><?php echo e($row['label']); ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?php echo e($row['description']); ?></p>
                <?php if($row['inherits_company']): ?>
                    <p class="mt-2 text-xs text-amber-700"><?php echo e(__('No branch override configured — company default applies.')); ?></p>
                <?php endif; ?>
            </div>
            <?php if($canManage): ?>
                <label class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2 text-sm">
                    <input type="hidden" name="rules[<?php echo e($row['rule_type']); ?>][is_enabled]" value="0">
                    <input
                        type="checkbox"
                        name="rules[<?php echo e($row['rule_type']); ?>][is_enabled]"
                        value="1"
                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        <?php if($row['is_enabled']): echo 'checked'; endif; ?>
                    >
                    <span class="font-medium text-slate-700"><?php echo e(__('Active')); ?></span>
                </label>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $row['is_enabled'] ? 'success' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['is_enabled'] ? 'success' : 'danger')]); ?>
                    <?php echo e($row['is_enabled'] ? __('Active') : __('Inactive')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if($canManage): ?>
        <div class="border-b border-erp-border bg-erp-page/40 px-5 py-4 sm:px-6">
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'min_'.$row['rule_type'],'value' => __('Minimum approvers')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('min_'.$row['rule_type']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Minimum approvers'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
            <input
                type="number"
                id="min_<?php echo e($row['rule_type']); ?>"
                name="rules[<?php echo e($row['rule_type']); ?>][min_approvers]"
                value="<?php echo e($row['min_approvers']); ?>"
                min="1"
                max="10"
                class="erp-input mt-1 w-24"
            >
        </div>
    <?php elseif($row['min_approvers'] > 1): ?>
        <div class="border-b border-erp-border px-5 py-3 sm:px-6 text-sm text-slate-600">
            <?php echo e(__('Minimum approvers')); ?>: <?php echo e($row['min_approvers']); ?>

        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="erp-table erp-table--grid">
            <thead>
                <tr>
                    <?php if($row['metric'] === 'amount' || $row['metric'] === 'both'): ?>
                        <th class="pl-5 sm:pl-6"><?php echo e(__('Amount ≥')); ?></th>
                    <?php endif; ?>
                    <?php if($row['metric'] === 'percent' || $row['metric'] === 'both'): ?>
                        <th><?php echo e(__('Percent ≥')); ?></th>
                    <?php endif; ?>
                    <th><?php echo e(__('Required role')); ?></th>
                    <th class="pr-5 sm:pr-6"><?php echo e(__('Required permission')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border bg-white">
                <?php $tiers = $row['tiers'] !== [] ? $row['tiers'] : ($row['company_tiers'] ?? []); ?>
                <?php $__empty_1 = true; $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/50">
                        <?php if($row['metric'] === 'amount' || $row['metric'] === 'both'): ?>
                            <td class="py-3 pl-5 sm:pl-6">
                                <?php if($canManage): ?>
                                    <input type="number" step="0.01" min="0" name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($index); ?>][threshold_amount]" value="<?php echo e($tier['threshold_amount'] ?? ''); ?>" class="erp-input w-32" placeholder="<?php echo e(__('Amount')); ?>">
                                <?php else: ?>
                                    <?php echo e($tier['threshold_amount'] ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <?php if($row['metric'] === 'percent' || $row['metric'] === 'both'): ?>
                            <td class="py-3">
                                <?php if($canManage): ?>
                                    <input type="number" step="0.01" min="0" max="100" name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($index); ?>][threshold_percent]" value="<?php echo e($tier['threshold_percent'] ?? ''); ?>" class="erp-input w-24" placeholder="%">
                                <?php else: ?>
                                    <?php echo e(isset($tier['threshold_percent']) ? $tier['threshold_percent'].'%' : '—'); ?>

                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="py-3">
                            <?php if($canManage): ?>
                                <select name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($index); ?>][approver_role]" class="erp-select w-full min-w-[10rem]">
                                    <option value=""><?php echo e(__('Any')); ?></option>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role); ?>" <?php if(($tier['approver_role'] ?? '') === $role): echo 'selected'; endif; ?>><?php echo e($role); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <?php echo e($tier['approver_role'] ?? '—'); ?>

                            <?php endif; ?>
                        </td>
                        <td class="py-3 pr-5 sm:pr-6">
                            <?php if($canManage): ?>
                                <select name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($index); ?>][approver_permission]" class="erp-select w-full min-w-[12rem]">
                                    <option value=""><?php echo e($row['default_permission'] ?? __('None')); ?></option>
                                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($permission); ?>" <?php if(($tier['approver_permission'] ?? $row['default_permission']) === $permission): echo 'selected'; endif; ?>><?php echo e($permission); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <?php echo e($tier['approver_permission'] ?? $row['default_permission'] ?? '—'); ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="py-4 pl-5 text-slate-500 sm:pl-6"><?php echo e(__('No tiers configured.')); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if($canManage): ?>
                    <?php for($i = count($tiers); $i < count($tiers) + 3; $i++): ?>
                        <tr class="bg-erp-page/40">
                            <?php if($row['metric'] === 'amount' || $row['metric'] === 'both'): ?>
                                <td class="py-3 pl-5 sm:pl-6">
                                    <input type="number" step="0.01" min="0" name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($i); ?>][threshold_amount]" class="erp-input w-32" placeholder="<?php echo e(collect($row['example_tiers'])->get($i - count($tiers))); ?>">
                                </td>
                            <?php endif; ?>
                            <?php if($row['metric'] === 'percent' || $row['metric'] === 'both'): ?>
                                <td class="py-3">
                                    <input type="number" step="0.01" min="0" max="100" name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($i); ?>][threshold_percent]" class="erp-input w-24" placeholder="<?php echo e(collect($row['example_tiers'])->get($i - count($tiers))); ?>">
                                </td>
                            <?php endif; ?>
                            <td class="py-3">
                                <select name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($i); ?>][approver_role]" class="erp-select w-full min-w-[10rem]">
                                    <option value=""><?php echo e(__('Any')); ?></option>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role); ?>"><?php echo e($role); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td class="py-3 pr-5 sm:pr-6">
                                <select name="rules[<?php echo e($row['rule_type']); ?>][tiers][<?php echo e($i); ?>][approver_permission]" class="erp-select w-full min-w-[12rem]">
                                    <option value=""><?php echo e($row['default_permission'] ?? __('None')); ?></option>
                                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($permission); ?>"><?php echo e($permission); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\approvals\partials\workspace-panel.blade.php ENDPATH**/ ?>