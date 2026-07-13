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

<div
    x-data="moduleWorkspaceShell()"
    @module-workspace-search.window="query = $event.detail?.query ?? ''"
    <?php echo e($attributes->merge(['class' => 'module-shell workspace-content-shell flex min-h-0 w-full min-w-0 flex-1 flex-col gap-2 overflow-hidden'])); ?>

>
    <?php if (isset($component)) { $__componentOriginal35d357500b9bf1947b480677203677da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal35d357500b9bf1947b480677203677da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.compact-workspace-header','data' => ['title' => $title,'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.compact-workspace-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
        <?php if(isset($search)): ?>
             <?php $__env->slot('search', null, []); ?> <?php echo e($search); ?> <?php $__env->endSlot(); ?>
        <?php endif; ?>
        <?php if(isset($actions)): ?>
             <?php $__env->slot('actions', null, []); ?> <?php echo e($actions); ?> <?php $__env->endSlot(); ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal35d357500b9bf1947b480677203677da)): ?>
<?php $attributes = $__attributesOriginal35d357500b9bf1947b480677203677da; ?>
<?php unset($__attributesOriginal35d357500b9bf1947b480677203677da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal35d357500b9bf1947b480677203677da)): ?>
<?php $component = $__componentOriginal35d357500b9bf1947b480677203677da; ?>
<?php unset($__componentOriginal35d357500b9bf1947b480677203677da); ?>
<?php endif; ?>

    <?php if(count($primaryWorkspaces) > 0): ?>
        <?php if (isset($component)) { $__componentOriginal09b310a68c9d2a0a2683b2f5e39742c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09b310a68c9d2a0a2683b2f5e39742c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-tabs','data' => ['workspaces' => $primaryWorkspaces,'active' => $activePrimary]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primaryWorkspaces),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activePrimary)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal09b310a68c9d2a0a2683b2f5e39742c1)): ?>
<?php $attributes = $__attributesOriginal09b310a68c9d2a0a2683b2f5e39742c1; ?>
<?php unset($__attributesOriginal09b310a68c9d2a0a2683b2f5e39742c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal09b310a68c9d2a0a2683b2f5e39742c1)): ?>
<?php $component = $__componentOriginal09b310a68c9d2a0a2683b2f5e39742c1; ?>
<?php unset($__componentOriginal09b310a68c9d2a0a2683b2f5e39742c1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(count($secondaryWorkspaces) > 1 || count($secondaryToolbarActions) > 0): ?>
        <div class="module-workspace-secondary-bar">
            <?php if(count($secondaryWorkspaces) > 1): ?>
                <?php if (isset($component)) { $__componentOriginalcf4041ffdc356dc2ea04813a0b1968ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf4041ffdc356dc2ea04813a0b1968ab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-sub-tabs','data' => ['class' => 'module-workspace-secondary-bar__tabs','workspaces' => $secondaryWorkspaces,'active' => $activeSecondary]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-sub-tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'module-workspace-secondary-bar__tabs','workspaces' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($secondaryWorkspaces),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeSecondary)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf4041ffdc356dc2ea04813a0b1968ab)): ?>
<?php $attributes = $__attributesOriginalcf4041ffdc356dc2ea04813a0b1968ab; ?>
<?php unset($__attributesOriginalcf4041ffdc356dc2ea04813a0b1968ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf4041ffdc356dc2ea04813a0b1968ab)): ?>
<?php $component = $__componentOriginalcf4041ffdc356dc2ea04813a0b1968ab; ?>
<?php unset($__componentOriginalcf4041ffdc356dc2ea04813a0b1968ab); ?>
<?php endif; ?>
            <?php else: ?>
                <div class="module-workspace-secondary-bar__tabs"></div>
            <?php endif; ?>

            <?php if(count($secondaryToolbarActions) > 0): ?>
                <div class="module-workspace-secondary-bar__actions">
                    <?php $__currentLoopData = $secondaryToolbarActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($action['modal'] ?? false): ?>
                            <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => $action['href'],'variant' => $action['variant'] ?? 'primary','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['href']),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['variant'] ?? 'primary'),'class' => 'shrink-0']); ?><?php echo e($action['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
                        <?php else: ?>
                            <a
                                href="<?php echo e($action['href']); ?>"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'shrink-0',
                                    ($action['variant'] ?? 'primary') === 'secondary' ? 'erp-btn-secondary' : 'erp-btn-primary',
                                ]); ?>"
                            ><?php echo e($action['label']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($kpis)): ?>
        <?php if (isset($component)) { $__componentOriginal5602281812c7dd97256c959080bb4e5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5602281812c7dd97256c959080bb4e5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-strip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($kpis); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $attributes = $__attributesOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__attributesOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $component = $__componentOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__componentOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(isset($actionBar)): ?>
        <?php if (isset($component)) { $__componentOriginal5eee171cb0310d49fa57c946de0a0b10 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5eee171cb0310d49fa57c946de0a0b10 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.action-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.action-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($actionBar); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5eee171cb0310d49fa57c946de0a0b10)): ?>
<?php $attributes = $__attributesOriginal5eee171cb0310d49fa57c946de0a0b10; ?>
<?php unset($__attributesOriginal5eee171cb0310d49fa57c946de0a0b10); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5eee171cb0310d49fa57c946de0a0b10)): ?>
<?php $component = $__componentOriginal5eee171cb0310d49fa57c946de0a0b10; ?>
<?php unset($__componentOriginal5eee171cb0310d49fa57c946de0a0b10); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($showContent): ?>
        <?php if (isset($component)) { $__componentOriginaldae5519fe02f63b83f66ee60c02b885f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae5519fe02f63b83f66ee60c02b885f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-content-shell','data' => ['url' => $contentUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-content-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contentUrl)]); ?>
            <?php if(isset($content)): ?>
                <?php echo e($content); ?>

            <?php else: ?>
                <?php echo e($slot); ?>

            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae5519fe02f63b83f66ee60c02b885f)): ?>
<?php $attributes = $__attributesOriginaldae5519fe02f63b83f66ee60c02b885f; ?>
<?php unset($__attributesOriginaldae5519fe02f63b83f66ee60c02b885f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae5519fe02f63b83f66ee60c02b885f)): ?>
<?php $component = $__componentOriginaldae5519fe02f63b83f66ee60c02b885f; ?>
<?php unset($__componentOriginaldae5519fe02f63b83f66ee60c02b885f); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/module-shell.blade.php ENDPATH**/ ?>