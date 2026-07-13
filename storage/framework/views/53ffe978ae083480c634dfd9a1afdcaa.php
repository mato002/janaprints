<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'formKey',
    'name' => 'status',
    'field' => [],
    'value' => null,
    'model' => null,
    'selectClass' => 'erp-input mt-1 w-full',
    'companyId' => null,
    'branchId' => null,
    'allowEmpty' => false,
    'emptyLabel' => null,
    'showLabel' => true,
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
    'formKey',
    'name' => 'status',
    'field' => [],
    'value' => null,
    'model' => null,
    'selectClass' => 'erp-input mt-1 w-full',
    'companyId' => null,
    'branchId' => null,
    'allowEmpty' => false,
    'emptyLabel' => null,
    'showLabel' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Platform\FormStatusOptionService;

    $statusService = app(FormStatusOptionService::class);
    $companyId ??= old('company_id', $model?->company_id ?? tenant()->companyId() ?? auth()->user()?->company_id);
    $branchId ??= old('branch_id', $model?->branch_id ?? tenant()->branchId());
    $options = $statusService->optionsFor($formKey, $companyId, $branchId);
    $selectedValue = FormStatusOptionService::valueOf(old($name, $value ?? ($field['default'] ?? null)));
    $label = __($field['label'] ?? __('Status'));
    $required = (bool) ($field['required'] ?? false);
    $readOnly = (bool) ($field['read_only'] ?? false);
    $inputId = $attributes->get('id', $name);

    if ($selectedValue && ! $options->contains(fn ($option) => $option->value === $selectedValue)) {
        $options->push(new \App\Models\Platform\FormStatusOption([
            'value' => $selectedValue,
            'label' => $statusService->labelFor($selectedValue, $formKey, $companyId, $branchId),
            'is_active' => false,
        ]));
    }

    $normalizedOptions = $options->map(fn ($option) => [
        'value' => $option->value,
        'label' => $option->label,
    ])->values()->all();

    $canCreate = ! $allowEmpty
        && ! $readOnly
        && $statusService->formHasConfigurableStatus($formKey)
        && (auth()->user()?->can('settings.manage') || auth()->user()?->can('update', new \App\Models\Platform\SettingsGovernance()))
        && Route::has('admin.form-statuses.quick-create');

    $scopeQuery = array_filter([
        'form_key' => $formKey,
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ], fn ($value) => $value !== null && $value !== '');

    $createUrl = $canCreate
        ? route('admin.form-statuses.quick-create', $scopeQuery)
        : null;
    $refreshUrl = Route::has('admin.lookups.form_statuses')
        ? route('admin.lookups.form_statuses', $scopeQuery)
        : null;
?>

<div
    <?php if($canCreate): ?>
        x-data="erpLookupCreate({
            name: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>,
            selected: <?php echo \Illuminate\Support\Js::from($selectedValue !== null && $selectedValue !== '' ? (string) $selectedValue : '')->toHtml() ?>,
            options: <?php echo \Illuminate\Support\Js::from($normalizedOptions)->toHtml() ?>,
            createUrl: <?php echo \Illuminate\Support\Js::from($createUrl)->toHtml() ?>,
            refreshUrl: <?php echo \Illuminate\Support\Js::from($refreshUrl)->toHtml() ?>,
            modalTitle: <?php echo \Illuminate\Support\Js::from(__('Add status'))->toHtml() ?>,
            scopeCompanyField: <?php echo \Illuminate\Support\Js::from('company_id')->toHtml() ?>,
            scopeBranchField: <?php echo \Illuminate\Support\Js::from('branch_id')->toHtml() ?>,
            scopeFormKey: <?php echo \Illuminate\Support\Js::from($formKey)->toHtml() ?>,
        })"
    <?php endif; ?>
    <?php echo e($attributes->except(['id', 'class'])->merge(['class' => ($showLabel ? 'erp-form-field ' : '').($canCreate ? 'erp-lookup-select' : '')])); ?>

>
    <?php if($showLabel): ?>
        <label for="<?php echo e($inputId); ?>" class="text-sm font-medium text-slate-700">
            <?php echo e($label); ?>

            <?php if($required && ! $allowEmpty): ?>
                <span class="text-red-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-lookup-select__row mt-1 flex items-stretch gap-2' => $canCreate, 'mt-1' => ! $canCreate && $showLabel]); ?>">
        <select
            id="<?php echo e($inputId); ?>"
            name="<?php echo e($name); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                $selectClass,
                'erp-lookup-select__input min-w-0 flex-1' => $canCreate,
            ]); ?>"
            <?php if($canCreate): ?> x-model="selected" <?php endif; ?>
            <?php if($required && ! $allowEmpty): echo 'required'; endif; ?>
            <?php if($readOnly): echo 'disabled'; endif; ?>
        >
            <?php if($allowEmpty): ?>
                <option value=""><?php echo e($emptyLabel ?? __('All')); ?></option>
            <?php endif; ?>
            <?php $__currentLoopData = $normalizedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option['value']); ?>" <?php if($selectedValue === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($canCreate): ?>
            <button
                type="button"
                class="erp-lookup-select__add"
                @click.stop.prevent="openCreate($event)"
                :aria-label="<?php echo \Illuminate\Support\Js::from(__('Add status'))->toHtml() ?>"
                :title="<?php echo \Illuminate\Support\Js::from(__('Add status'))->toHtml() ?>"
            >
                <span aria-hidden="true">+</span>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/form-status-select.blade.php ENDPATH**/ ?>