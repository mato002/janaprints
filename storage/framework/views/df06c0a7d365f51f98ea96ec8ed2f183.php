<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'primaryWorkspaces' => [],
    'activePrimary' => null,
    'secondaryWorkspaces' => [],
    'activeSecondary' => null,
    'secondaryToolbarActions' => [],
    'contextWorkspaces' => [],
    'activeContext' => null,
    'contentUrl' => null,
    'showContent' => true,
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
    'title',
    'description' => null,
    'primaryWorkspaces' => [],
    'activePrimary' => null,
    'secondaryWorkspaces' => [],
    'activeSecondary' => null,
    'secondaryToolbarActions' => [],
    'contextWorkspaces' => [],
    'activeContext' => null,
    'contentUrl' => null,
    'showContent' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal5f67761b765e1a48051660f8c011fc7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5f67761b765e1a48051660f8c011fc7a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.module-shell','data' => ['title' => $title,'description' => $description,'primaryWorkspaces' => $primaryWorkspaces,'activePrimary' => $activePrimary,'secondaryWorkspaces' => $secondaryWorkspaces,'activeSecondary' => $activeSecondary,'secondaryToolbarActions' => $secondaryToolbarActions,'contextWorkspaces' => $contextWorkspaces,'activeContext' => $activeContext,'contentUrl' => $contentUrl,'showContent' => $showContent,'attributes' => $attributes->except([
        'title',
        'description',
        'primaryWorkspaces',
        'activePrimary',
        'secondaryWorkspaces',
        'activeSecondary',
        'secondaryToolbarActions',
        'contextWorkspaces',
        'activeContext',
        'contentUrl',
        'showContent',
    ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.module-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description),'primary-workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primaryWorkspaces),'active-primary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activePrimary),'secondary-workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($secondaryWorkspaces),'active-secondary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeSecondary),'secondary-toolbar-actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($secondaryToolbarActions),'context-workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contextWorkspaces),'active-context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeContext),'content-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentUrl),'show-content' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showContent),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->except([
        'title',
        'description',
        'primaryWorkspaces',
        'activePrimary',
        'secondaryWorkspaces',
        'activeSecondary',
        'secondaryToolbarActions',
        'contextWorkspaces',
        'activeContext',
        'contentUrl',
        'showContent',
    ]))]); ?>
    <?php if(isset($search)): ?>
         <?php $__env->slot('search', null, []); ?> <?php echo e($search); ?> <?php $__env->endSlot(); ?>
    <?php endif; ?>

    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5f67761b765e1a48051660f8c011fc7a)): ?>
<?php $attributes = $__attributesOriginal5f67761b765e1a48051660f8c011fc7a; ?>
<?php unset($__attributesOriginal5f67761b765e1a48051660f8c011fc7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5f67761b765e1a48051660f8c011fc7a)): ?>
<?php $component = $__componentOriginal5f67761b765e1a48051660f8c011fc7a; ?>
<?php unset($__componentOriginal5f67761b765e1a48051660f8c011fc7a); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\workspace-shell.blade.php ENDPATH**/ ?>