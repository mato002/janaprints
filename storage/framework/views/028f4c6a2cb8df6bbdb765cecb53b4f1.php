<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hidden' => false,
    'createRoute' => null,
    'refreshRoute' => null,
    'permission' => null,
    'modalTitle' => null,
    'optionLabelKey' => 'name',
    'optionValueKey' => 'id',
    'selectClass' => 'erp-select mt-1',
    'emptyOption' => true,
    'emptyLabel' => null,
    'scopeCompanyField' => null,
    'scopeBranchField' => null,
    'scopeCustomerField' => null,
]));

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

foreach (array_filter(([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hidden' => false,
    'createRoute' => null,
    'refreshRoute' => null,
    'permission' => null,
    'modalTitle' => null,
    'optionLabelKey' => 'name',
    'optionValueKey' => 'id',
    'selectClass' => 'erp-select mt-1',
    'emptyOption' => true,
    'emptyLabel' => null,
    'scopeCompanyField' => null,
    'scopeBranchField' => null,
    'scopeCustomerField' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $normalizedOptions = collect($options)->map(function ($option) use ($optionLabelKey, $optionValueKey) {
        if (is_array($option)) {
            return [
                'value' => $option['value'] ?? $option[$optionValueKey] ?? '',
                'label' => $option['label'] ?? $option[$optionLabelKey] ?? '',
            ];
        }

        return [
            'value' => data_get($option, $optionValueKey),
            'label' => (string) data_get($option, $optionLabelKey),
        ];
    })->values()->all();

    $selectedValue = old($name, $value);
    $isReadOnly = (bool) $readonly;
    $isDisabled = (bool) $disabled || $isReadOnly;
    $permissions = collect(explode('|', (string) $permission))->filter()->values()->all();
    $canCreate = $createRoute
        && Route::has($createRoute)
        && ($permissions === [] || collect($permissions)->contains(fn ($perm) => auth()->user()?->can($perm)))
        && ! $isDisabled
        && ! $isReadOnly;
    $createUrl = $canCreate ? route($createRoute) : null;
    $refreshUrl = $refreshRoute && Route::has($refreshRoute) ? route($refreshRoute) : null;
    $fieldId = $attributes->get('id', $name);
?>

<?php if(! $hidden): ?>
<div
    x-data="erpLookupCreate({
        name: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
        selected: <?php echo \Illuminate\Support\Js::from($selectedValue !== null && $selectedValue !== '' ? (string) $selectedValue : '')->toHtml() ?>,
        options: <?php echo \Illuminate\Support\Js::from($normalizedOptions)->toHtml() ?>,
        createUrl: <?php echo \Illuminate\Support\Js::from($createUrl)->toHtml() ?>,
        refreshUrl: <?php echo \Illuminate\Support\Js::from($refreshUrl)->toHtml() ?>,
        modalTitle: <?php echo \Illuminate\Support\Js::from($modalTitle ?? $label)->toHtml() ?>,
        scopeCompanyField: <?php echo \Illuminate\Support\Js::from($scopeCompanyField)->toHtml() ?>,
        scopeBranchField: <?php echo \Illuminate\Support\Js::from($scopeBranchField)->toHtml() ?>,
        scopeCustomerField: <?php echo \Illuminate\Support\Js::from($scopeCustomerField)->toHtml() ?>,
    })"
    <?php echo e($attributes->class(['erp-lookup-select'])); ?>

>
    <?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => $fieldId,'value' => $label,'required' => $required && ! $isReadOnly]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fieldId),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required && ! $isReadOnly)]); ?>
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
    <?php endif; ?>
    <div class="erp-lookup-select__row mt-1 flex items-stretch gap-2">
        <select
            id="<?php echo e($fieldId); ?>"
            name="<?php echo e($name); ?>"
            class="<?php echo e($selectClass); ?> erp-lookup-select__input min-w-0 flex-1"
            x-model="selected"
            data-empty-option="<?php echo e($emptyOption ? '1' : '0'); ?>"
            <?php if($placeholder): ?> data-placeholder="<?php echo e($emptyLabel ?? $placeholder ?? __('Select')); ?>" <?php endif; ?>
            <?php if($required && ! $isReadOnly): echo 'required'; endif; ?>
            <?php if($isDisabled): echo 'disabled'; endif; ?>
        >
            <?php if($emptyOption): ?>
                <option value=""><?php echo e($emptyLabel ?? $placeholder ?? __('Select')); ?></option>
            <?php endif; ?>
            <?php $__currentLoopData = $normalizedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option['value']); ?>" <?php if((string) ($selectedValue ?? '') === (string) $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($canCreate): ?>
            <button
                type="button"
                class="erp-lookup-select__add"
                @click.stop.prevent="openCreate($event)"
                :aria-label="<?php echo \Illuminate\Support\Js::from(__('Add new').' '.($modalTitle ?? $label ?? $name))->toHtml() ?>"
                :title="<?php echo \Illuminate\Support\Js::from(__('Add new').' '.($modalTitle ?? $label ?? $name))->toHtml() ?>"
            >
                <span aria-hidden="true">+</span>
            </button>
        <?php endif; ?>
    </div>
    <?php if (isset($component)) { $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.field-error','data' => ['name' => $name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name)]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/lookup-select.blade.php ENDPATH**/ ?>