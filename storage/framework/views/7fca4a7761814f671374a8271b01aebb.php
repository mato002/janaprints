<?php
    use App\Support\Sales\SalesDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = SalesDeskViews::normalize($activeSalesView ?? request('view', SalesDeskViews::DESK));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => SalesDeskViews::DESK,
            'label' => __('Walk-in'),
            'url' => SalesDeskViews::deskUrl(SalesDeskViews::DESK),
            'visible' => ($user?->can('crm.customers.create') || $user?->can('sales_orders.create')) ?? false,
        ],
        [
            'key' => SalesDeskViews::QUOTES,
            'label' => __('Quotes'),
            'url' => SalesDeskViews::quotesUrl(),
            'visible' => $user?->can('quotations.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::ORDERS,
            'label' => __('Orders'),
            'url' => SalesDeskViews::ordersUrl(),
            'visible' => $user?->can('sales_orders.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::ARTWORK,
            'label' => __('Artwork'),
            'url' => SalesDeskViews::artworkUrl(),
            'visible' => $user?->can('artwork.view') ?? false,
        ],
        [
            'key' => SalesDeskViews::APPROVALS,
            'label' => __('Approvals'),
            'url' => SalesDeskViews::approvalsUrl(),
            'visible' => $user?->can('commercial.approvals.view') ?? false,
        ],
    ])->where('visible', true)->values();
?>

<?php if($modes->count() > 1): ?>
    <nav class="workspace-context-tabs" aria-label="<?php echo e(__('Sales desk modes')); ?>">
        <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e(WorkspaceEmbed::url($mode['url'])); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'workspace-context-tab',
                    'workspace-context-tab--active' => $mode['key'] === $active,
                ]); ?>"
                data-turbo-frame="<?php echo e($frame); ?>"
                data-turbo-action="advance"
            ><?php echo e($mode['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\desk-mode-nav.blade.php ENDPATH**/ ?>