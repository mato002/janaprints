<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url' => null,
    'frameId' => 'module-workspace-content',
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
    'url' => null,
    'frameId' => 'module-workspace-content',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'module-workspace-content flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-hidden'])); ?>>
    <?php if($url): ?>
        <turbo-frame
            id="<?php echo e($frameId); ?>"
            src="<?php echo e($url); ?>"
            class="module-workspace-content__frame flex min-h-0 flex-1 flex-col overflow-hidden"
            data-turbo-action="replace"
            data-turbo-cache="false"
        >
            <div class="module-workspace-content__loading" aria-live="polite">
                <div class="erp-skeleton module-workspace-content__skeleton-bar"></div>
                <div class="erp-skeleton module-workspace-content__skeleton-panel"></div>
            </div>
        </turbo-frame>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/workspace-content.blade.php ENDPATH**/ ?>