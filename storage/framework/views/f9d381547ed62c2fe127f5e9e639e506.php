<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'payroll_group',
    'value' => null,
    'groups' => collect(),
    'label' => null,
    'required' => true,
    'selectClass' => 'erp-input w-full',
    'scopeCompanyField' => null,
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
    'name' => 'payroll_group',
    'value' => null,
    'groups' => collect(),
    'label' => null,
    'required' => true,
    'selectClass' => 'erp-input w-full',
    'scopeCompanyField' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => $name,'label' => $label ?? __('Payroll group'),'options' => $groups,'optionValueKey' => 'code','optionLabelKey' => 'name','value' => $value,'required' => $required,'emptyOption' => false,'selectClass' => $selectClass,'createRoute' => 'admin.payroll-groups.quick-create','refreshRoute' => 'admin.lookups.payroll_groups','permission' => 'hr.compensation.create','modalTitle' => __('Create payroll group'),'scopeCompanyField' => $scopeCompanyField]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label ?? __('Payroll group')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groups),'option-value-key' => 'code','option-label-key' => 'name','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'select-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectClass),'create-route' => 'admin.payroll-groups.quick-create','refresh-route' => 'admin.lookups.payroll_groups','permission' => 'hr.compensation.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create payroll group')),'scope-company-field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scopeCompanyField)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\compensation\partials\payroll-group-select.blade.php ENDPATH**/ ?>