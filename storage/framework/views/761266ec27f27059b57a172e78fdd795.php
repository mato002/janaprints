<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'customer',
    'latestOrderForRepeat' => null,
    'buttonClass' => 'crm-360__btn crm-360__btn--outline',
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
    'customer',
    'latestOrderForRepeat' => null,
    'buttonClass' => 'crm-360__btn crm-360__btn--outline',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="relative" x-data="{ open: false }">
    <button
        type="button"
        class="<?php echo e($buttonClass); ?>"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
    >
        <?php echo e(__('Actions')); ?>

        <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="crm-360__more-menu"
        role="menu"
    >
        <?php echo $__env->make('admin.crm.customers.360.partials.customer-actions-menu-items', [
            'customer' => $customer,
            'latestOrderForRepeat' => $latestOrderForRepeat,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\partials\customer-actions-dropdown.blade.php ENDPATH**/ ?>