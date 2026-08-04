<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $customer = $tabData['customer'] ?? ($jobCard->customer ? [
        'name' => $jobCard->customer->company_name,
        'code' => $jobCard->customer->customer_code,
    ] : null);
    $salesOrder = $tabData['sales_order'] ?? ($jobCard->salesOrder ? ['number' => $jobCard->salesOrder->order_number] : null);
    $quotation = $tabData['quotation'] ?? ($jobCard->quotation ? ['number' => $jobCard->quotation->quotation_number] : null);
    $artwork = $tabData['artwork'] ?? ($jobCard->artworkRequest ? ['number' => $jobCard->artworkRequest->request_number] : null);

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);

    $commercialUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'commercial']);
?>

<div class="job-360-commercial-chips" aria-label="<?php echo e(__('Commercial links')); ?>">
    <?php if($jobCard->salesOrder || $salesOrder): ?>
        <?php if($jobCard->salesOrder && auth()->user()?->can('view', $jobCard->salesOrder)): ?>
            <a
                href="<?php echo e(route('admin.sales-orders.show', $jobCard->salesOrder)); ?>"
                class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link"
                title="<?php echo e(__('Sales order')); ?>"
                <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            >
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('SO')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($salesOrder['number'] ?? $jobCard->salesOrder->order_number); ?></span>
            </a>
        <?php else: ?>
            <span class="job-360-commercial-chips__chip" title="<?php echo e(__('Sales order')); ?>">
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('SO')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($salesOrder['number'] ?? $jobCard->salesOrder?->order_number); ?></span>
            </span>
        <?php endif; ?>
    <?php else: ?>
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="<?php echo e(__('Sales order')); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('SO')); ?></span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    <?php endif; ?>

    <?php if($jobCard->quotation || $quotation): ?>
        <?php if($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation)): ?>
            <a href="<?php echo e(route('admin.quotations.show', $jobCard->quotation)); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Quotation')); ?>">
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('QT')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($quotation['number'] ?? $jobCard->quotation->quotation_number); ?></span>
            </a>
        <?php else: ?>
            <span class="job-360-commercial-chips__chip" title="<?php echo e(__('Quotation')); ?>">
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('QT')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($quotation['number']); ?></span>
            </span>
        <?php endif; ?>
    <?php else: ?>
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="<?php echo e(__('Quotation')); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('QT')); ?></span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    <?php endif; ?>

    <?php if($jobCard->artworkRequest || $artwork): ?>
        <?php if($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest)): ?>
            <a href="<?php echo e(route('admin.artwork.show', $jobCard->artworkRequest)); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Artwork')); ?>">
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('AW')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($artwork['number'] ?? $jobCard->artworkRequest->request_number); ?></span>
            </a>
        <?php else: ?>
            <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork'])); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Artwork')); ?>">
                <span class="job-360-commercial-chips__abbr"><?php echo e(__('AW')); ?></span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value"><?php echo e($artwork['number']); ?></span>
            </a>
        <?php endif; ?>
    <?php else: ?>
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="<?php echo e(__('Artwork')); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('AW')); ?></span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    <?php endif; ?>

    <?php if($hasDeliveryNote && $deliveryNote): ?>
        <a href="<?php echo e($deliveryNote['show_url'] ?? '#'); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link font-mono" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Delivery note')); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('DN')); ?></span>
            <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
            <span class="job-360-commercial-chips__value"><?php echo e($deliveryNote['delivery_note_number'] ?? '—'); ?></span>
        </a>
    <?php else: ?>
        <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch'])); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Delivery note')); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('DN')); ?></span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </a>
    <?php endif; ?>

    <a href="<?php echo e($commercialUrl); ?>" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> title="<?php echo e(__('Commercial tab')); ?>">
        <span class="job-360-commercial-chips__abbr"><?php echo e(__('$')); ?></span>
        <span class="job-360-commercial-chips__value"><?php echo e(__('Cost')); ?></span>
    </a>

    <?php if($customer): ?>
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--customer" title="<?php echo e($customer['code'] ?? ''); ?>">
            <span class="job-360-commercial-chips__abbr"><?php echo e(__('Cust')); ?></span>
            <span class="job-360-commercial-chips__value"><?php echo e($customer['name']); ?></span>
        </span>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\commercial-chips.blade.php ENDPATH**/ ?>