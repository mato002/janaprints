<?php
    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;

    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);
?>

<section class="job-360-zone job-360-zone--commercial" aria-label="<?php echo e(__('Commercial')); ?>">
    <header class="job-360-zone__head">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'receipt-tax','class' => 'h-5 w-5 text-violet-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'receipt-tax','class' => 'h-5 w-5 text-violet-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
        <h2 class="job-360-zone__title"><?php echo e(__('Commercial')); ?></h2>
    </header>

    <ul class="job-360-commercial-links">
        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label"><?php echo e(__('Sales order')); ?></span>
            <?php if($jobCard->salesOrder): ?>
                <a
                    href="<?php echo e(route('admin.sales-orders.show', $jobCard->salesOrder)); ?>"
                    class="job-360-commercial-links__value job-360-commercial-links__value--link"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                >
                    <?php echo e($salesOrder['number'] ?? $jobCard->salesOrder->order_number); ?>

                </a>
                <span class="job-360-commercial-links__meta"><?php echo e(str_replace('_', ' ', $salesOrder['status'] ?? $jobCard->salesOrder->status->value)); ?></span>
            <?php elseif($salesOrder): ?>
                <span class="job-360-commercial-links__value"><?php echo e($salesOrder['number']); ?></span>
            <?php else: ?>
                <span class="job-360-commercial-links__empty"><?php echo e(__('Not linked')); ?></span>
            <?php endif; ?>
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label"><?php echo e(__('Quotation')); ?></span>
            <?php if($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation)): ?>
                <a href="<?php echo e(route('admin.quotations.show', $jobCard->quotation)); ?>" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    <?php echo e($quotation['number'] ?? $jobCard->quotation->quotation_number); ?>

                </a>
            <?php elseif($quotation): ?>
                <span class="job-360-commercial-links__value"><?php echo e($quotation['number']); ?></span>
            <?php else: ?>
                <span class="job-360-commercial-links__empty"><?php echo e(__('Not linked')); ?></span>
            <?php endif; ?>
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label"><?php echo e(__('Artwork')); ?></span>
            <?php if($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest)): ?>
                <a href="<?php echo e(route('admin.artwork.show', $jobCard->artworkRequest)); ?>" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    <?php echo e($artwork['number'] ?? $jobCard->artworkRequest->request_number); ?>

                </a>
                <span class="job-360-commercial-links__meta"><?php echo e(str_replace('_', ' ', $artwork['status'] ?? $jobCard->artworkRequest->status->value)); ?></span>
            <?php elseif($artwork): ?>
                <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork'])); ?>" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    <?php echo e($artwork['number']); ?>

                </a>
            <?php else: ?>
                <span class="job-360-commercial-links__empty"><?php echo e(__('Not linked')); ?></span>
            <?php endif; ?>
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label"><?php echo e(__('Delivery note')); ?></span>
            <?php if($hasDeliveryNote && $deliveryNote): ?>
                <a href="<?php echo e($deliveryNote['show_url'] ?? '#'); ?>" class="job-360-commercial-links__value font-mono" data-turbo-frame="erp-main">
                    <?php echo e($deliveryNote['delivery_note_number'] ?? '—'); ?>

                </a>
            <?php else: ?>
                <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch'])); ?>" class="job-360-commercial-links__value job-360-commercial-links__value--muted" data-turbo-frame="erp-main">
                    <?php echo e(__('Open dispatch')); ?>

                </a>
            <?php endif; ?>
        </li>

        <?php if($customer): ?>
            <li class="job-360-commercial-links__item job-360-commercial-links__item--secondary">
                <span class="job-360-commercial-links__label"><?php echo e(__('Customer')); ?></span>
                <span class="job-360-commercial-links__value"><?php echo e($customer['name']); ?></span>
                <span class="job-360-commercial-links__meta"><?php echo e($customer['code'] ?? ''); ?></span>
            </li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/commercial-zone.blade.php ENDPATH**/ ?>