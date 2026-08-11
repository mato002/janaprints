<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['form', 'canManage', 'position' => 'bottom', 'bare' => false]));

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

foreach (array_filter((['form', 'canManage', 'position' => 'bottom', 'bare' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($canManage): ?>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'px-4 py-3 sm:px-5' => $bare,
            'border-erp-border bg-violet-50/60 px-5 py-4 sm:px-6' => ! $bare,
            'border-b' => ! $bare && $position === 'top',
            'border-t' => ! $bare && $position === 'bottom',
        ]); ?>"
        <?php if (! ($bare)): ?> id="add-custom-field" <?php endif; ?>
    >
        <?php if (! ($bare)): ?>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Add custom field')); ?></h3>
                    <p class="mt-1 text-xs text-slate-500">
                        <?php echo e(__('Use lowercase keys with underscores (e.g. tax_id). Fill in the fields below, then click Save form settings.')); ?>

                    </p>
                </div>
                <?php if($position === 'bottom'): ?>
                    <a href="#add-custom-field" class="text-xs font-medium text-erp-accent hover:underline"><?php echo e(__('Jump here')); ?></a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="mb-3 text-xs text-slate-500">
                <?php echo e(__('Use lowercase keys with underscores (e.g. tax_id), then save the form.')); ?>

            </p>
        <?php endif; ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4', 'mt-3' => ! $bare]); ?>">
            <div>
                <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'add_field_key_'.e($form['form_key']).'','value' => __('Field key')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'add_field_key_'.e($form['form_key']).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Field key'))]); ?>
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
                    type="text"
                    id="add_field_key_<?php echo e($form['form_key']); ?>"
                    name="forms[<?php echo e($form['form_key']); ?>][add_field][field_key]"
                    class="erp-input mt-1 w-full font-mono text-sm"
                    placeholder="custom_field"
                    pattern="[a-z][a-z0-9_]*"
                >
            </div>
            <div>
                <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'add_field_label_'.e($form['form_key']).'','value' => __('Label')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'add_field_label_'.e($form['form_key']).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Label'))]); ?>
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
                    type="text"
                    id="add_field_label_<?php echo e($form['form_key']); ?>"
                    name="forms[<?php echo e($form['form_key']); ?>][add_field][label]"
                    class="erp-input mt-1 w-full"
                    placeholder="<?php echo e(__('Display label')); ?>"
                >
            </div>
            <div>
                <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'add_field_type_'.e($form['form_key']).'','value' => __('Type')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'add_field_type_'.e($form['form_key']).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Type'))]); ?>
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
                <select id="add_field_type_<?php echo e($form['form_key']); ?>" name="forms[<?php echo e($form['form_key']); ?>][add_field][type]" class="erp-select mt-1 w-full">
                    <option value="text"><?php echo e(__('Text')); ?></option>
                    <option value="email"><?php echo e(__('Email')); ?></option>
                    <option value="number"><?php echo e(__('Number')); ?></option>
                    <option value="date"><?php echo e(__('Date')); ?></option>
                    <option value="textarea"><?php echo e(__('Textarea')); ?></option>
                    <option value="select"><?php echo e(__('Select')); ?></option>
                    <option value="checkbox"><?php echo e(__('Checkbox')); ?></option>
                </select>
            </div>
            <?php if (! ($bare)): ?>
                <div class="flex items-end">
                    <p class="text-xs text-slate-500"><?php echo e(__('New fields appear in the table above after saving.')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/forms/partials/add-custom-field-panel.blade.php ENDPATH**/ ?>