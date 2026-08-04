<?php
    use App\Support\Procurement\ProcurementDeskViews;
?>

<?php switch($activeProcurementView ?? ProcurementDeskViews::DESK):
    case (ProcurementDeskViews::REQUESTS): ?>
        <?php echo $__env->make('admin.procurement.requests.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (ProcurementDeskViews::SUPPLIERS): ?>
        <?php echo $__env->make('admin.procurement.vendors.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (ProcurementDeskViews::RFQS): ?>
        <?php echo $__env->make('admin.procurement.rfqs.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (ProcurementDeskViews::ORDERS): ?>
        <?php echo $__env->make('admin.procurement.orders.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (ProcurementDeskViews::RECEIPTS): ?>
        <?php echo $__env->make('admin.procurement.receipts.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (ProcurementDeskViews::APPROVALS): ?>
        <?php echo $__env->make('admin.procurement.approvals.partials.register-content', ['embeddedInDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
<?php endswitch; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\desk\partials\register-panel.blade.php ENDPATH**/ ?>