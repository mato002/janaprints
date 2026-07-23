<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'employees',
    'value' => null,
    'required' => true,
    'selectClass' => 'erp-select mt-1 w-full',
    'allowCreate' => true,
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
    'employees',
    'value' => null,
    'required' => true,
    'selectClass' => 'erp-select mt-1 w-full',
    'allowCreate' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $employeeOptions = collect($employees)->map(fn ($employee) => [
        'id' => $employee->id,
        'name' => trim("{$employee->first_name} {$employee->last_name}")." ({$employee->employee_number})",
    ]);
?>

<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'employee_id','label' => __('Employee'),'options' => $employeeOptions,'value' => old('employee_id', $value),'required' => $required,'createRoute' => $allowCreate ? 'admin.employees.quick-create' : null,'refreshRoute' => 'admin.lookups.employees','permission' => $allowCreate ? 'employees.manage' : null,'modalTitle' => __('Create employee'),'optionLabelKey' => 'name','selectClass' => $selectClass,'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'employee_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Employee')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employeeOptions),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('employee_id', $value)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'create-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($allowCreate ? 'admin.employees.quick-create' : null),'refresh-route' => 'admin.lookups.employees','permission' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($allowCreate ? 'employees.manage' : null),'modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create employee')),'option-label-key' => 'name','select-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectClass),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\partials\employee-lookup-select.blade.php ENDPATH**/ ?>