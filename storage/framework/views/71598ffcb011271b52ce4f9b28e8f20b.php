<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'companyId',
    'branchId' => null,
    'companies',
    'branches',
    'branchLabel' => __('Branch scope'),
    'branchEmptyLabel' => __('Company default'),
    'compact' => false,
    'activeFormKey' => null,
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
    'action',
    'companyId',
    'branchId' => null,
    'companies',
    'branches',
    'branchLabel' => __('Branch scope'),
    'branchEmptyLabel' => __('Company default'),
    'compact' => false,
    'activeFormKey' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $embedded = WorkspaceEmbed::inWorkspaceContext();
    $scopeAction = $embedded ? WorkspaceEmbed::url($action) : $action;
    $compact = $compact || $embedded;
?>

<?php if($companies->count() > 1 || $branches->isNotEmpty()): ?>
    <?php if($compact): ?>
        <form method="GET" action="<?php echo e($scopeAction); ?>" class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
            <form method="GET" action="<?php echo e($scopeAction); ?>" class="flex flex-wrap items-end gap-4">
    <?php endif; ?>
            <?php if($embedded): ?>
                <input type="hidden" name="embedded" value="1">
            <?php endif; ?>
            <?php if($companies->count() > 1): ?>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex items-center gap-2' => $compact, 'min-w-[12rem]' => ! $compact, 'flex-1' => ! $compact]); ?>">
                    <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'company_id','value' => __('Company'),'class' => \Illuminate\Support\Arr::toCssClasses(['shrink-0 text-xs font-medium text-slate-500' => $compact])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'company_id','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company')),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(['shrink-0 text-xs font-medium text-slate-500' => $compact]))]); ?>
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
                    <select id="company_id" name="company_id" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-select', 'w-full min-w-[10rem] py-1.5 text-sm' => $compact, 'mt-1 w-full' => ! $compact]); ?>" onchange="this.form.submit()">
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($company->id); ?>" <?php if($companyId === $company->id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
            <?php endif; ?>

            <?php if($activeFormKey): ?>
                <input type="hidden" name="form" value="<?php echo e($activeFormKey); ?>">
            <?php endif; ?>

            <?php if($branches->isNotEmpty()): ?>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex items-center gap-2' => $compact, 'min-w-[12rem]' => ! $compact, 'flex-1' => ! $compact]); ?>">
                    <?php if($compact): ?>
                        <label for="branch_id" class="shrink-0 text-xs font-medium text-slate-500"><?php echo e($branchLabel); ?></label>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'branch_id','value' => $branchLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'branch_id','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branchLabel)]); ?>
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
                    <select id="branch_id" name="branch_id" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-select', 'w-full min-w-[10rem] py-1.5 text-sm' => $compact, 'mt-1 w-full' => ! $compact]); ?>" onchange="this.form.submit()">
                        <option value=""><?php echo e($branchEmptyLabel); ?></option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php if($branchId === $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>
        </form>
    <?php if (! ($compact)): ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/partials/scope-selector.blade.php ENDPATH**/ ?>