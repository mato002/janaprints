<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'customer',
    'latestOrderForRepeat' => null,
    'closeOnClick' => true,
    'omitPrimary' => false,
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
    'closeOnClick' => true,
    'omitPrimary' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $close = $closeOnClick ? '@click="open = false"' : '';
?>

<?php if (! ($omitPrimary)): ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.customers.start', $customer)); ?>" class="block" data-turbo-frame="erp-main">
            <?php echo csrf_field(); ?>
            <button type="submit" class="crm-360__more-item w-full text-left" role="menuitem" <?php echo $close; ?>>
                <?php echo e(__('Start conversation')); ?>

            </button>
        </form>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quotations.create')): ?>
        <a
            href="<?php echo e(route('admin.quotations.create', ['customer_id' => $customer->id])); ?>"
            class="crm-360__more-item"
            role="menuitem"
            data-turbo-frame="erp-form-modal"
            <?php echo $close; ?>

        ><?php echo e(__('Create Quotation')); ?></a>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.create')): ?>
        <a
            href="<?php echo e(route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct'])); ?>"
            class="crm-360__more-item"
            role="menuitem"
            data-turbo-frame="erp-form-modal"
            <?php echo $close; ?>

        ><?php echo e(__('Create Direct Order')); ?></a>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.create')): ?>
    <a
        href="<?php echo e(route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'quotation'])); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-form-modal"
        <?php echo $close; ?>

    ><?php echo e(__('Create from Quotation')); ?></a>
    <?php if(! empty($latestOrderForRepeat)): ?>
        <form
            method="POST"
            action="<?php echo e(route('admin.crm.customers.repeat-order', [$customer, $latestOrderForRepeat])); ?>"
            class="block"
            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Create a repeat order from :number?', ['number' => $latestOrderForRepeat->order_number]))->toHtml() ?>)"
        >
            <?php echo csrf_field(); ?>
            <button type="submit" class="crm-360__more-item w-full text-left" role="menuitem" <?php echo $close; ?>>
                <?php echo e(__('Repeat Order')); ?>

            </button>
        </form>
    <?php endif; ?>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
    <a
        href="<?php echo e(route('admin.crm.customers.edit', $customer)); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        <?php echo $close; ?>

    ><?php echo e(__('Edit customer')); ?></a>
    <a
        href="<?php echo e(route('admin.crm.customers.edit', $customer)); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        <?php echo $close; ?>

    ><?php echo e(__('Manage contacts')); ?></a>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('notes'); open = false"
    ><?php echo e(__('Add note')); ?></button>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('files'); open = false"
    ><?php echo e(__('Upload file')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.create')): ?>
    <a
        href="<?php echo e(route('admin.payments.create', ['customer_id' => $customer->id])); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        <?php echo $close; ?>

    ><?php echo e(__('Receive Payment')); ?></a>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewReceivablesStatement', App\Models\Crm\Customer::class)): ?>
    <a
        href="<?php echo e(route('admin.receivables.statement', [
            'customer_id' => $customer->id,
            'from_date' => now()->subYear()->toDateString(),
            'to_date' => now()->toDateString(),
        ])); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        <?php echo $close; ?>

    ><?php echo e(__('View Statement')); ?></a>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crm.customers.view')): ?>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('print-specifications'); open = false"
    ><?php echo e(__('Print Specifications')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.view')): ?>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    ><?php echo e(__('View Orders')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoices.view')): ?>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    ><?php echo e(__('View Invoices')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoices.create')): ?>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('commercial'); open = false"
    ><?php echo e(__('Create invoice')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Crm\CustomerActivity::class)): ?>
    <button
        type="button"
        class="crm-360__more-item w-full text-left"
        role="menuitem"
        @click="setTab('activities'); open = false"
    ><?php echo e(__('Schedule follow-up')); ?></button>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
    <a
        href="<?php echo e(route('admin.crm.customers.edit', $customer)); ?>"
        class="crm-360__more-item"
        role="menuitem"
        data-turbo-frame="erp-main"
        <?php echo $close; ?>

    ><?php echo e(__('Assign account manager')); ?></a>
<?php endif; ?>

<hr class="crm-360__more-divider">

<a
    href="<?php echo e(route('admin.crm.customers.edit', $customer)); ?>"
    class="crm-360__more-item"
    role="menuitem"
    data-turbo-frame="erp-main"
    <?php echo $close; ?>

><?php echo e(__('View full profile')); ?></a>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/360/partials/customer-actions-menu-items.blade.php ENDPATH**/ ?>