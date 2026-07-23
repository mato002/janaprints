<?php
    // Reuse the same primary action computed in header scope via a light recompute for mobile.
    use App\Enums\SalesOrderStatus;
    use App\Models\Sales\CustomerInvoice;

    $canConfirm = ($workflow['can_confirm'] ?? false) && auth()->user()?->can('confirm', $salesOrder);
    $canRelease = ($workflow['can_release'] ?? false) && auth()->user()?->can('production', $salesOrder);
    $canClose = ($workflow['can_close'] ?? false) && auth()->user()?->can('close', $salesOrder);
    $canTransition = auth()->user()?->can('transition', $salesOrder);
    $canInvoice = auth()->user()?->can('create', CustomerInvoice::class)
        && $salesOrder->remainingInvoiceTotal() > 0
        && ! in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true);
    $onHold = $salesOrder->status === SalesOrderStatus::OnHold;

    $primary = null;
    if ($canConfirm) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.confirm', $salesOrder), 'label' => __('Confirm order')];
    } elseif ($canRelease) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.release-to-production', $salesOrder), 'label' => __('Send to production')];
    } elseif ($onHold && $canTransition) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.resume', $salesOrder), 'label' => __('Resume')];
    } elseif ($canInvoice) {
        $primary = ['type' => 'link', 'url' => route('admin.invoices.from-sales-order', $salesOrder), 'label' => __('Generate invoice'), 'modal' => true];
    } elseif ($canClose) {
        $primary = ['type' => 'form', 'action' => route('admin.sales-orders.close', $salesOrder), 'label' => __('Close order')];
    }
?>

<?php if($primary): ?>
    <div class="so-360__mobile-bar">
        <?php echo $__env->make('admin.sales.orders.workspace.partials.primary-action', ['primary' => $primary], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\workspace\mobile-action-bar.blade.php ENDPATH**/ ?>