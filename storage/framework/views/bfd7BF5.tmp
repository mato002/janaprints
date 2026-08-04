<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'steps' => [],
    'label' => null,
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
    'steps' => [],
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<nav class="rw-workflow" aria-label="<?php echo e($label ?? __('Workflow progress')); ?>">
    <ol class="rw-workflow__track" role="list">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $state = $step['state'] ?? 'future'; // done | current | future
            ?>
            <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rw-workflow__step', 'rw-workflow__step--'.$state]); ?>">
                <span class="rw-workflow__marker" aria-hidden="true">
                    <?php if($state === 'done'): ?>
                        ✓
                    <?php else: ?>
                        <?php echo e($loop->iteration); ?>

                    <?php endif; ?>
                </span>
                <span class="rw-workflow__label"><?php echo e($step['label']); ?></span>
                <?php if(! empty($step['url']) && $state !== 'future'): ?>
                    <a
                        href="<?php echo e($step['url']); ?>"
                        class="rw-workflow__link"
                        data-turbo-frame="erp-main"
                        <?php if($state === 'current'): ?> aria-current="step" <?php endif; ?>
                    >
                        <span class="sr-only"><?php echo e($step['label']); ?></span>
                    </a>
                <?php elseif($state === 'current'): ?>
                    <span class="sr-only"><?php echo e(__('Current step')); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/record-workspace/workflow.blade.php ENDPATH**/ ?>