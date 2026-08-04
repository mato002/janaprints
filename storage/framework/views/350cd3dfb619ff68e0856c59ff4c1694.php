<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);

    $badges = [];

    if ($jobCard->salesOrder || $salesOrder) {
        $badges[] = [
            'theme' => 'materials',
            'label' => __('Sales order'),
            'value' => $salesOrder['number'] ?? $jobCard->salesOrder?->order_number,
            'url' => $jobCard->salesOrder ? route('admin.sales-orders.show', $jobCard->salesOrder) : null,
        ];
    }

    if ($jobCard->quotation || $quotation) {
        $badges[] = [
            'theme' => 'commercial',
            'label' => __('Quote'),
            'value' => $quotation['number'] ?? $jobCard->quotation?->quotation_number,
            'url' => ($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation))
                ? route('admin.quotations.show', $jobCard->quotation)
                : null,
        ];
    }

    if ($jobCard->artworkRequest || $artwork) {
        $badges[] = [
            'theme' => 'qc',
            'label' => __('Artwork'),
            'value' => $artwork['number'] ?? $jobCard->artworkRequest?->request_number,
            'url' => ($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest))
                ? route('admin.artwork.show', $jobCard->artworkRequest)
                : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']),
        ];
    }

    $badges[] = [
        'theme' => 'dispatch',
        'label' => __('Dispatch'),
        'value' => ($hasDeliveryNote && $deliveryNote)
            ? ($deliveryNote['delivery_note_number'] ?? '—')
            : __('Open dispatch'),
        'url' => ($hasDeliveryNote && $deliveryNote)
            ? ($deliveryNote['show_url'] ?? '#')
            : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
    ];
?>

<?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'commercial','title' => __('Commercial'),'icon' => 'currency-dollar','compact' => true,'ariaLabel' => ''.e(__('Commercial')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'commercial','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Commercial')),'icon' => 'currency-dollar','compact' => true,'aria-label' => ''.e(__('Commercial')).'']); ?>
    <div class="space-y-2">
        <?php $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($badge['url']): ?>
                <a
                    href="<?php echo e($badge['url']); ?>"
                    class="job-360-commercial-badge job-360-commercial-badge--<?php echo e($badge['theme']); ?>"
                    <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                >
                    <span class="job-360-commercial-badge__label"><?php echo e($badge['label']); ?></span>
                    <span class="job-360-commercial-badge__value"><?php echo e($badge['value']); ?></span>
                </a>
            <?php else: ?>
                <div class="job-360-commercial-badge job-360-commercial-badge--<?php echo e($badge['theme']); ?>">
                    <span class="job-360-commercial-badge__label"><?php echo e($badge['label']); ?></span>
                    <span class="job-360-commercial-badge__value"><?php echo e($badge['value']); ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($customer): ?>
            <div class="job-360-commercial-badge job-360-commercial-badge--slate">
                <span class="job-360-commercial-badge__label"><?php echo e(__('Customer')); ?></span>
                <span class="job-360-commercial-badge__value"><?php echo e($customer['name']); ?></span>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\commercial-zone.blade.php ENDPATH**/ ?>