<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'companies',
    'value' => null,
    'selectClass' => 'erp-select mt-1',
    'required' => true,
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
    'companies',
    'value' => null,
    'selectClass' => 'erp-select mt-1',
    'required' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(auth()->user()->hasRole('Super Admin')): ?>
    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'company_id','label' => __('Company'),'options' => $companies,'value' => old('company_id', $value ?? $companies->first()?->id),'required' => $required,'createRoute' => 'admin.companies.quick-create','refreshRoute' => 'admin.lookups.companies','permission' => 'companies.manage','modalTitle' => __('Create company'),'optionLabelKey' => 'name','optionValueKey' => 'id','selectClass' => $selectClass,'emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'company_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($companies),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('company_id', $value ?? $companies->first()?->id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'create-route' => 'admin.companies.quick-create','refresh-route' => 'admin.lookups.companies','permission' => 'companies.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create company')),'option-label-key' => 'name','option-value-key' => 'id','select-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectClass),'empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
<?php else: ?>
    <input type="hidden" name="company_id" value="<?php echo e(auth()->user()->company_id); ?>">
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\lookup-company-select.blade.php ENDPATH**/ ?>