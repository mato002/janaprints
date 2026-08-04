<?php
    use App\Support\Sales\SalesDeskViews;
?>

<?php switch($activeSalesView ?? SalesDeskViews::DESK):
    case (SalesDeskViews::QUOTES): ?>
        <?php echo $__env->make('admin.sales.quotations.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (SalesDeskViews::ORDERS): ?>
        <?php echo $__env->make('admin.sales.orders.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (SalesDeskViews::ARTWORK): ?>
        <?php echo $__env->make('admin.artwork.requests.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (SalesDeskViews::APPROVALS): ?>
        <?php echo $__env->make('admin.commercial.approvals.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
<?php endswitch; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\register-panel.blade.php ENDPATH**/ ?>