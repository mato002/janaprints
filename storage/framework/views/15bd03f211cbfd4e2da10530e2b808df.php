<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'groups' => [],
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
    'groups' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="rw-actions" aria-label="<?php echo e(__('Record actions')); ?>">
    <div class="rw-actions__inner">
        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($group['items'])) continue; ?>
            <div class="rw-actions__group" data-group="<?php echo e($group['key'] ?? $loop->index); ?>">
                <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(! empty($action['html'])): ?>
                        <?php echo $action['html']; ?>

                    <?php elseif(! empty($action['onclick'])): ?>
                        <button
                            type="button"
                            class="rw-actions__btn rw-actions__btn--<?php echo e($action['variant'] ?? 'ghost'); ?>"
                            onclick="<?php echo e($action['onclick']); ?>"
                        ><?php echo e($action['label']); ?></button>
                    <?php else: ?>
                        <a
                            href="<?php echo e($action['url']); ?>"
                            class="rw-actions__btn rw-actions__btn--<?php echo e($action['variant'] ?? 'outline'); ?>"
                            <?php if(! empty($action['external'])): ?> target="_blank" rel="noopener" <?php elseif(! str_starts_with((string) ($action['url'] ?? ''), '#')): ?> data-turbo-frame="erp-main" <?php endif; ?>
                        ><?php echo e($action['label']); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/record-workspace/action-bar.blade.php ENDPATH**/ ?>