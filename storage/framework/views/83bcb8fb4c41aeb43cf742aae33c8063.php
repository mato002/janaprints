<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['story']));

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

foreach (array_filter((['story']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="public-success-story" data-animate="fade-up">
    <div class="public-success-story__header">
        <span class="public-success-story__type"><?php echo e($story['client_type']); ?></span>
        <h3 class="public-success-story__title"><?php echo e($story['title']); ?></h3>
    </div>

    <dl class="public-success-story__details">
        <div class="public-success-story__row">
            <dt>Challenge</dt>
            <dd><?php echo e($story['challenge']); ?></dd>
        </div>
        <div class="public-success-story__row">
            <dt>Solution</dt>
            <dd><?php echo e($story['solution']); ?></dd>
        </div>
        <div class="public-success-story__row public-success-story__row--outcome">
            <dt>Outcome</dt>
            <dd><?php echo e($story['outcome']); ?></dd>
        </div>
    </dl>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/success-story.blade.php ENDPATH**/ ?>