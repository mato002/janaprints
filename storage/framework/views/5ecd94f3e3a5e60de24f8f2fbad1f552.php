<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['form', 'canManage', 'backUrl' => null]));

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

foreach (array_filter((['form', 'canManage', 'backUrl' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => '!p-0 overflow-hidden','id' => 'form-panel-'.e($form['form_key']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 overflow-hidden','id' => 'form-panel-'.e($form['form_key']).'']); ?>
    <div class="border-b border-erp-border bg-erp-page/30 px-4 py-2 sm:px-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                <?php if($backUrl): ?>
                    <a
                        href="<?php echo e($backUrl); ?>"
                        data-turbo-action="advance"
                        class="inline-flex shrink-0 items-center gap-1 font-medium text-slate-500 transition-colors hover:text-erp-accent"
                    >
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-left','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'h-3.5 w-3.5']); ?>
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
                        <?php echo e(__('All forms')); ?>

                    </a>
                    <span class="text-slate-300" aria-hidden="true">/</span>
                <?php endif; ?>
                <span class="font-semibold text-erp-primary"><?php echo e($form['label']); ?></span>
                <?php if($form['inherits_company']): ?>
                    <span class="text-xs text-amber-700"><?php echo e(__('Company default')); ?></span>
                <?php endif; ?>
            </div>
            <?php if($canManage): ?>
                <label class="inline-flex shrink-0 items-center gap-2 text-sm">
                    <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][is_active]" value="0">
                    <input
                        type="checkbox"
                        name="forms[<?php echo e($form['form_key']); ?>][is_active]"
                        value="1"
                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        <?php if($form['is_active']): echo 'checked'; endif; ?>
                    >
                    <span class="font-medium text-slate-600"><?php echo e(__('Form active')); ?></span>
                </label>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $form['is_active'] ? 'success' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($form['is_active'] ? 'success' : 'danger')]); ?>
                    <?php echo e($form['is_active'] ? __('Active') : __('Inactive')); ?>

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

    <div class="overflow-x-auto">
        <table class="erp-table erp-table--grid">
            <thead>
                <tr>
                    <th class="pl-4 sm:pl-5"><?php echo e(__('Field')); ?></th>
                    <th><?php echo e(__('Visibility')); ?></th>
                    <th><?php echo e(__('Requirement')); ?></th>
                    <th><?php echo e(__('Read only')); ?></th>
                    <th><?php echo e(__('Default value')); ?></th>
                    <?php if($canManage): ?>
                        <th class="pr-4 sm:pr-5 text-right" title="<?php echo e(__('Remove is only available for custom fields you added.')); ?>"><?php echo e(__('Actions')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border bg-white">
                <?php $__currentLoopData = $form['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2 pl-4 font-medium text-slate-700 sm:pl-5">
                            <div class="flex items-center gap-2">
                                <span><?php echo e(__($field['label'])); ?></span>
                                <?php if(! empty($field['is_custom'])): ?>
                                    <span class="rounded bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-violet-700"><?php echo e(__('Custom')); ?></span>
                                <?php else: ?>
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('System')); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="block font-mono text-xs font-normal text-slate-400"><?php echo e($field['field_key']); ?></span>
                        </td>
                        <td class="py-2">
                            <?php if($canManage): ?>
                                <?php
                                    $visibilityLocked = ($field['required'] && ! ($field['registry_required'] ?? false)) || ($field['registry_required'] ?? false);
                                    $visibilityValue = $field['hidden'] ? 'hidden' : 'visible';
                                ?>
                                <?php if($visibilityLocked): ?>
                                    <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][visibility]" value="<?php echo e($visibilityValue); ?>">
                                <?php endif; ?>
                                <select
                                    name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][visibility]"
                                    class="erp-select form-field-visibility w-full min-w-[7rem] max-w-[9rem]"
                                    data-registry-required="<?php echo e(($field['registry_required'] ?? false) ? '1' : '0'); ?>"
                                    <?php if($visibilityLocked): echo 'disabled'; endif; ?>
                                >
                                    <option value="visible" <?php if($field['visible']): echo 'selected'; endif; ?>><?php echo e(__('Visible')); ?></option>
                                    <option value="hidden" <?php if($field['hidden']): echo 'selected'; endif; ?>><?php echo e(__('Hidden')); ?></option>
                                </select>
                                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'form-field-visibility-hint mt-1 text-[10px] text-slate-400',
                                    'hidden' => ! ($field['required'] || ($field['registry_required'] ?? false)),
                                ]); ?>"><?php echo e(__('Required fields stay visible')); ?></p>
                            <?php else: ?>
                                <span class="text-slate-600"><?php echo e($field['hidden'] ? __('Hidden') : __('Visible')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if($canManage): ?>
                                <?php if($field['registry_required'] ?? false): ?>
                                    <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][requirement]" value="required">
                                <?php endif; ?>
                                <select
                                    name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][requirement]"
                                    class="erp-select form-field-requirement w-full min-w-[7rem] max-w-[9rem]"
                                    data-registry-required="<?php echo e(($field['registry_required'] ?? false) ? '1' : '0'); ?>"
                                    <?php if($field['registry_required'] ?? false): echo 'disabled'; endif; ?>
                                >
                                    <option value="required" <?php if($field['required']): echo 'selected'; endif; ?>><?php echo e(__('Required')); ?></option>
                                    <option value="optional" <?php if(! $field['required']): echo 'selected'; endif; ?> <?php if($field['registry_required'] ?? false): echo 'disabled'; endif; ?>><?php echo e(__('Optional')); ?></option>
                                </select>
                                <?php if($field['registry_required'] ?? false): ?>
                                    <p class="mt-1 text-[10px] text-slate-400"><?php echo e(__('System field')); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-slate-600"><?php echo e($field['required'] ? __('Required') : __('Optional')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2 text-center">
                            <?php if($canManage): ?>
                                <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][read_only]" value="0">
                                <input
                                    type="checkbox"
                                    name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][read_only]"
                                    value="1"
                                    class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                    <?php if($field['read_only']): echo 'checked'; endif; ?>
                                >
                            <?php else: ?>
                                <span class="text-slate-600"><?php echo e($field['read_only'] ? __('Yes') : __('No')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if($canManage): ?>
                                <input
                                    type="text"
                                    name="forms[<?php echo e($form['form_key']); ?>][fields][<?php echo e($field['field_key']); ?>][default_value]"
                                    value="<?php echo e($field['default'] ?? ''); ?>"
                                    class="erp-input w-full min-w-[8rem] max-w-xs"
                                    placeholder="—"
                                >
                            <?php else: ?>
                                <span class="text-slate-600"><?php echo e($field['default'] ?? '—'); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if($canManage): ?>
                            <td class="py-2 pr-4 text-right sm:pr-5">
                                <?php if(! empty($field['is_custom'])): ?>
                                    <label class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                                        <input
                                            type="checkbox"
                                            name="forms[<?php echo e($form['form_key']); ?>][remove_fields][]"
                                            value="<?php echo e($field['field_key']); ?>"
                                            class="rounded border-red-300 text-red-600 focus:ring-red-500"
                                        >
                                        <?php echo e(__('Remove')); ?>

                                    </label>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400" title="<?php echo e(__('Built-in system fields cannot be removed. Use visibility to hide them instead.')); ?>"><?php echo e(__('Built-in')); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <?php echo $__env->make('admin.settings.forms.partials.status-options-panel', [
        'form' => $form,
        'canManage' => $canManage,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($canManage): ?>
        <details class="group border-t border-erp-border bg-violet-50/40">
            <summary class="cursor-pointer list-none px-4 py-2 text-sm font-medium text-erp-primary hover:bg-violet-50/80 sm:px-5 [&::-webkit-details-marker]:hidden">
                <span class="inline-flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-down','class' => 'h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0']); ?>
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
                    <?php echo e(__('Add custom field')); ?>

                </span>
            </summary>
            <div id="add-custom-field">
                <?php echo $__env->make('admin.settings.forms.partials.add-custom-field-panel', [
                    'form' => $form,
                    'canManage' => $canManage,
                    'position' => 'bottom',
                    'bare' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </details>

        <div class="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-2 border-t border-erp-border bg-erp-card px-4 py-2 sm:px-5">
            <p class="text-xs text-slate-500">
                <?php echo e(__('Built-in fields are system-defined. Custom fields are stored for your tenant.')); ?>

            </p>
            <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'button','dataErpFormSettingsSave' => true,'dataSavingLabel' => ''.e(__('Saving…')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','data-erp-form-settings-save' => true,'data-saving-label' => ''.e(__('Saving…')).'']); ?>
                <?php echo e(__('Save form settings')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>
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

<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\forms\partials\workspace-panel.blade.php ENDPATH**/ ?>