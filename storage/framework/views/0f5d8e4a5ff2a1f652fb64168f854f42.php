<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'can_export' => false,
    'export_route' => null,
    'export_query' => null,
    'export_route_params' => [],
    'format_in_path' => false,
    'post_action' => null,
    'post_fields' => [],
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
    'can_export' => false,
    'export_route' => null,
    'export_query' => null,
    'export_route_params' => [],
    'format_in_path' => false,
    'post_action' => null,
    'post_fields' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => $can_export ? $export_route : null,'exportQuery' => $export_query ?? request()->query(),'exportRouteParams' => $export_route_params,'formatInPath' => $format_in_path,'postAction' => $can_export ? $post_action : null,'postFields' => $post_fields,'canExport' => $can_export]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($can_export ? $export_route : null),'export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($export_query ?? request()->query()),'export-route-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($export_route_params),'format-in-path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($format_in_path),'post-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($can_export ? $post_action : null),'post-fields' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post_fields),'can-export' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($can_export)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/reports/partials/export-button.blade.php ENDPATH**/ ?>