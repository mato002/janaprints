<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'eyebrow' => null,
    'backUrl' => null,
    'backLabel' => null,
    'title',
    'subtitle' => null,
    'meta' => [],
    'metrics' => [],
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
    'eyebrow' => null,
    'backUrl' => null,
    'backLabel' => null,
    'title',
    'subtitle' => null,
    'meta' => [],
    'metrics' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header class="rw-header">
    <div class="rw-header__top">
        <?php if($backUrl): ?>
            <a href="<?php echo e($backUrl); ?>" class="rw-header__back" data-turbo-frame="erp-main">
                ← <?php echo e($backLabel ?? __('Back')); ?>

            </a>
        <?php endif; ?>

        <?php if(isset($badges) || ! $slot->isEmpty()): ?>
            <div class="rw-header__badges">
                <?php if(isset($badges)): ?>
                    <?php echo e($badges); ?>

                <?php else: ?>
                    <?php echo e($slot); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rw-header__body">
        <div class="rw-header__identity">
            <?php if($eyebrow): ?>
                <p class="rw-header__eyebrow"><?php echo e($eyebrow); ?></p>
            <?php endif; ?>

            <h1 class="rw-header__title"><?php echo e($title); ?></h1>

            <?php if($subtitle): ?>
                <p class="rw-header__subtitle"><?php echo e($subtitle); ?></p>
            <?php endif; ?>

            <?php if($meta !== []): ?>
                <div class="rw-header__meta">
                    <?php $__currentLoopData = $meta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="rw-header__meta-item"><?php echo e($item); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if($metrics !== [] || isset($metricsSlot)): ?>
            <dl class="rw-header__metrics">
                <?php if(isset($metricsSlot)): ?>
                    <?php echo e($metricsSlot); ?>

                <?php else: ?>
                    <?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <dt><?php echo e($metric['label']); ?></dt>
                            <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rw-header__metric-value', $metric['class'] ?? null]); ?>"><?php echo e($metric['value']); ?></dd>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </dl>
        <?php endif; ?>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/record-workspace/header.blade.php ENDPATH**/ ?>