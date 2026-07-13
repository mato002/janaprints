<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'right',
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
    'align' => 'right',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    class="relative inline-block text-left"
    data-erp-row-actions
    x-data="erpRowActionsMenu(<?php echo \Illuminate\Support\Js::from($align)->toHtml() ?>)"
    @click.outside="closeFromOutside()"
    @keydown.escape.window="close()"
    @scroll.window="close()"
    @resize.window="close()"
    @erp-row-menu-close="close()"
>
    <button
        type="button"
        x-ref="trigger"
        data-erp-row-actions-trigger
        @click.stop="toggle($event)"
        class="erp-row-actions-trigger inline-flex h-8 w-8 items-center justify-center rounded-md border border-transparent text-slate-500 transition-colors hover:border-erp-border hover:bg-erp-page hover:text-erp-primary"
        :aria-expanded="open"
        aria-haspopup="true"
        aria-label="<?php echo e(__('Row actions')); ?>"
    >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>

    <div
        x-ref="menu"
        :style="open ? menuStyle : null"
        :class="open ? 'erp-row-actions-menu--open' : ''"
        data-erp-row-actions-menu
        class="erp-row-actions-menu min-w-[12rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
        role="menu"
    >
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/table-row-actions.blade.php ENDPATH**/ ?>