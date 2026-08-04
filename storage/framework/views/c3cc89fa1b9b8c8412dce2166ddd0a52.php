<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['fieldKey', 'field', 'lead' => null]));

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

foreach (array_filter((['fieldKey', 'field', 'lead' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\LeadStatus;

    $required = (bool) ($field['required'] ?? false);
    $readOnly = (bool) ($field['read_only'] ?? false);
    $label = __($field['label'] ?? $fieldKey);
    $type = $field['type'] ?? 'text';

    $modelValue = match ($fieldKey) {
        'status' => $lead?->status?->value,
        'expected_close_date' => $lead?->expected_close_date?->format('Y-m-d'),
        default => $lead?->{$fieldKey} ?? null,
    };

    $value = old($fieldKey, $modelValue ?? ($field['default'] ?? ''));
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'erp-form-field',
    'md:col-span-2' => $type === 'textarea',
]); ?>">
    <?php switch($fieldKey):
        case ('lead_source_id'): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
            <select
                id="<?php echo e($fieldKey); ?>"
                name="<?php echo e($fieldKey); ?>"
                class="erp-select mt-1 w-full"
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readOnly): echo 'disabled'; endif; ?>
            >
                <option value=""><?php echo e(__('None')); ?></option>
                <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($source->id); ?>" <?php if((string) $value === (string) $source->id): echo 'selected'; endif; ?>><?php echo e($source->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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
            <?php break; ?>

        <?php case ('stage_id'): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
            <select
                id="<?php echo e($fieldKey); ?>"
                name="<?php echo e($fieldKey); ?>"
                class="erp-select mt-1 w-full"
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readOnly): echo 'disabled'; endif; ?>
            >
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($stage->id); ?>" <?php if((string) $value === (string) $stage->id): echo 'selected'; endif; ?>><?php echo e($stage->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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
            <?php break; ?>

        <?php case ('status'): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
            <select
                id="<?php echo e($fieldKey); ?>"
                name="<?php echo e($fieldKey); ?>"
                class="erp-select mt-1 w-full"
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readOnly): echo 'disabled'; endif; ?>
            >
                <?php $__currentLoopData = ($statuses ?? LeadStatus::cases()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if((string) $value === (string) $status->value): echo 'selected'; endif; ?>><?php echo e($status->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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
            <?php break; ?>

        <?php case ('assigned_to'): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
            <select
                id="<?php echo e($fieldKey); ?>"
                name="<?php echo e($fieldKey); ?>"
                class="erp-select mt-1 w-full"
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readOnly): echo 'disabled'; endif; ?>
            >
                <option value=""><?php echo e(__('Unassigned')); ?></option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if((string) $value === (string) $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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
            <?php break; ?>

        <?php case ('notes'): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
                id="<?php echo e($fieldKey); ?>"
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
            <?php break; ?>

        <?php default: ?>
            <?php
                $inputType = match ($type) {
                    'email' => 'email',
                    'number' => 'number',
                    'date' => 'date',
                    default => 'text',
                };
            ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => ''.e($fieldKey).'','value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($fieldKey).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
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
            <?php if($fieldKey === 'estimated_value'): ?>
                <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => $fieldKey,'name' => $fieldKey,'type' => 'number','step' => '0.01','class' => 'block mt-1 w-full','value' => $value,'required' => $required,'readonly' => $readOnly]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'type' => 'number','step' => '0.01','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readOnly)]); ?>
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
            <?php elseif($fieldKey === 'probability'): ?>
                <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => $fieldKey,'name' => $fieldKey,'type' => 'number','min' => '0','max' => '100','class' => 'block mt-1 w-full','value' => $value,'required' => $required,'readonly' => $readOnly]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'type' => 'number','min' => '0','max' => '100','class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readOnly)]); ?>
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
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => $fieldKey,'name' => $fieldKey,'type' => $inputType,'class' => 'block mt-1 w-full','value' => $value,'required' => $required,'readonly' => $readOnly]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldKey),'type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputType),'class' => 'block mt-1 w-full','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readOnly)]); ?>
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
    <?php endswitch; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\partials\registry-field.blade.php ENDPATH**/ ?>