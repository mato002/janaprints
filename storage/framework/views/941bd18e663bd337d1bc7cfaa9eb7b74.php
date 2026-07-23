<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['item', 'index' => 0, 'open' => false]));

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

foreach (array_filter((['item', 'index' => 0, 'open' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="public-conversion-faq__item" data-faq-item>
    <h3>
        <button
            type="button"
            class="public-conversion-faq__trigger"
            data-faq-trigger
            aria-expanded="<?php echo e($open ? 'true' : 'false'); ?>"
            id="faq-trigger-<?php echo e($index); ?>"
            aria-controls="faq-panel-<?php echo e($index); ?>"
        >
            <span><?php echo e($item['question']); ?></span>
            <span class="public-conversion-faq__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </button>
    </h3>
    <div
        class="public-conversion-faq__panel"
        data-faq-panel
        id="faq-panel-<?php echo e($index); ?>"
        role="region"
        aria-labelledby="faq-trigger-<?php echo e($index); ?>"
        <?php if(! $open): ?> hidden <?php endif; ?>
    >
        <p><?php echo e($item['answer']); ?></p>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\conversion-faq-item.blade.php ENDPATH**/ ?>