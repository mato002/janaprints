<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white border border-erp-border']));

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

foreach (array_filter((['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white border border-erp-border']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
?>

<div
    class="relative z-20 inline-block text-left"
    x-data="erpRowActionsMenu(<?php echo \Illuminate\Support\Js::from($align)->toHtml() ?>)"
    @click.outside="closeFromOutside()"
    @keydown.escape.window="close()"
    @scroll.window="close()"
    @resize.window="close()"
>
    <div x-ref="trigger" @click.stop="toggle($event)">
        <?php echo e($trigger); ?>

    </div>

    <div
        x-ref="menu"
        :style="open ? menuStyle : null"
        :class="open ? 'erp-row-actions-menu--open' : ''"
        class="erp-row-actions-menu <?php echo e($width); ?> rounded-xl shadow-card-hover"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 <?php echo e($contentClasses); ?>">
            <?php echo e($content); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\dropdown.blade.php ENDPATH**/ ?>