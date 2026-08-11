<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['fields' => [], 'model' => null, 'formKey' => null]));

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

foreach (array_filter((['fields' => [], 'model' => null, 'formKey' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $customFields = collect($fields)->filter(
        fn (array $field) => ($field['is_custom'] ?? false)
            && ! ($field['registry_required'] ?? false)
            && ($field['visible'] ?? true),
    );
?>

<?php if($customFields->isNotEmpty()): ?>
    <div class="md:col-span-2 border-t border-erp-border pt-4 mt-2">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-violet-700"><?php echo e(__('Custom fields')); ?></p>
        <div class="erp-form-grid">
            <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $inputId = 'custom_'.$fieldKey;
                    $value = old($fieldKey, $field['default'] ?? '');
                    $required = $field['required'] ?? false;
                    $readOnly = $field['read_only'] ?? false;
                ?>

                <?php if(($field['type'] ?? 'text') === 'textarea'): ?>
                    <div class="erp-form-field md:col-span-2">
                        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => $inputId,'value' => __($field['label']),'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputId),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__($field['label'])),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
                        <textarea
                            id="<?php echo e($inputId); ?>"
                            name="<?php echo e($fieldKey); ?>"
                            class="erp-input mt-1 w-full"
                            rows="3"
                            <?php if($required): echo 'required'; endif; ?>
                            <?php if($readOnly): echo 'readonly'; endif; ?>
                        ><?php echo e($value); ?></textarea>
                        <?php if (isset($component)) { $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.field-error','data' => ['name' => $fieldKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $attributes = $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $component = $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
                    </div>
                <?php elseif(($field['is_status_field'] ?? false) && $formKey): ?>
                    <?php if (isset($component)) { $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-status-select','data' => ['formKey' => $formKey,'name' => $fieldKey,'field' => $field,'value' => old($fieldKey, $model?->{$fieldKey} ?? ($field['default'] ?? null)),'model' => $model,'selectClass' => 'erp-input mt-1 w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-status-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formKey),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($field),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old($fieldKey, $model?->{$fieldKey} ?? ($field['default'] ?? null))),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($model),'select-class' => 'erp-input mt-1 w-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $attributes = $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $component = $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
                <?php elseif(($field['type'] ?? 'text') === 'checkbox'): ?>
                    <div class="flex items-center gap-2">
                        <input
                            type="hidden"
                            name="<?php echo e($fieldKey); ?>"
                            value="0"
                            <?php if($readOnly): echo 'disabled'; endif; ?>
                        >
                        <input
                            type="checkbox"
                            id="<?php echo e($inputId); ?>"
                            name="<?php echo e($fieldKey); ?>"
                            value="1"
                            class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                            <?php if(filter_var($value, FILTER_VALIDATE_BOOLEAN)): echo 'checked'; endif; ?>
                            <?php if($readOnly): echo 'disabled'; endif; ?>
                        >
                        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => $inputId,'value' => __($field['label']),'class' => '!mb-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputId),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__($field['label'])),'class' => '!mb-0']); ?>
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
                        <?php if (isset($component)) { $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.field-error','data' => ['name' => $fieldKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $attributes = $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $component = $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="erp-form-field">
                        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => $inputId,'value' => __($field['label']),'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputId),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__($field['label'])),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
                        <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => $inputId,'name' => $fieldKey,'class' => 'block mt-1 w-full','type' => match ($field['type'] ?? 'text') {
                                'email' => 'email',
                                'number' => 'number',
                                'date' => 'date',
                                default => 'text',
                            },'value' => $value,'required' => $required,'readonly' => $readOnly]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputId),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'class' => 'block mt-1 w-full','type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($field['type'] ?? 'text') {
                                'email' => 'email',
                                'number' => 'number',
                                'date' => 'date',
                                default => 'text',
                            }),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readOnly)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.field-error','data' => ['name' => $fieldKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $attributes = $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $component = $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/partials/form-custom-fields.blade.php ENDPATH**/ ?>