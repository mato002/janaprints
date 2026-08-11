<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'customer',
    'latestOrderForRepeat' => null,
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $canStartConversation = auth()->user()->can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class);
    $canCreateQuote = auth()->user()->can('quotations.create');
    $canCreateOrder = auth()->user()->can('sales_orders.create');
?>

<div class="crm-360__action-bar" role="toolbar" aria-label="<?php echo e(__('Customer actions')); ?>">
    <?php if($canStartConversation): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.customers.start', $customer)); ?>" class="crm-360__action-primary" data-turbo-frame="erp-main">
            <?php echo csrf_field(); ?>
            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">
                <?php echo e(__('Start conversation')); ?>

            </button>
        </form>
    <?php endif; ?>

    <?php if($canCreateQuote): ?>
        <a
            href="<?php echo e(route('admin.quotations.create', ['customer_id' => $customer->id])); ?>"
            class="crm-360__btn crm-360__btn--outline crm-360__btn--sm crm-360__action-secondary"
            data-turbo-frame="erp-form-modal"
        ><?php echo e(__('Create quote')); ?></a>
    <?php endif; ?>

    <?php if($canCreateOrder): ?>
        <a
            href="<?php echo e(route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct'])); ?>"
            class="crm-360__btn crm-360__btn--outline crm-360__btn--sm crm-360__action-secondary"
            data-turbo-frame="erp-form-modal"
        ><?php echo e(__('New order')); ?></a>
    <?php endif; ?>

    <?php echo $__env->make('admin.crm.customers.360.partials.customer-actions-dropdown', [
        'customer' => $customer,
        'latestOrderForRepeat' => $latestOrderForRepeat,
        'buttonClass' => 'crm-360__btn crm-360__btn--ghost crm-360__btn--sm',
        'buttonLabel' => __('More'),
        'omitPrimary' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/360/partials/primary-actions.blade.php ENDPATH**/ ?>