<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo e(__('Session :number', ['number' => $session->session_number])); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td, th { padding: 4px 0; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1><?php echo e(__('POS Session Summary')); ?></h1>
    <p class="muted"><?php echo e($session->session_number); ?> · <?php echo e($session->branch?->name); ?></p>

    <table>
        <tr><td><?php echo e(__('Cashier')); ?></td><td class="right"><?php echo e($session->cashier?->name); ?></td></tr>
        <tr><td><?php echo e(__('Terminal')); ?></td><td class="right"><?php echo e($session->terminal ?? '—'); ?></td></tr>
        <tr><td><?php echo e(__('Opened')); ?></td><td class="right"><?php echo e($session->opened_at?->format('Y-m-d H:i')); ?></td></tr>
        <tr><td><?php echo e(__('Closed')); ?></td><td class="right"><?php echo e($session->closed_at?->format('Y-m-d H:i') ?? '—'); ?></td></tr>
        <tr><td><?php echo e(__('Opening float')); ?></td><td class="right"><?php echo e(number_format($session->opening_float, 2)); ?></td></tr>
        <tr><td><?php echo e(__('Status')); ?></td><td class="right"><?php echo e(ucfirst(str_replace('_', ' ', $session->status->value))); ?></td></tr>
    </table>

    <h2><?php echo e(__('Sales summary')); ?></h2>
    <table>
        <tr><td><?php echo e(__('Paid sales')); ?></td><td class="right"><?php echo e($metrics['sales_count']); ?></td></tr>
        <tr><td><?php echo e(__('Transactions')); ?></td><td class="right"><?php echo e($metrics['transactions_count']); ?></td></tr>
        <tr><td><?php echo e(__('Total sales value')); ?></td><td class="right"><?php echo e(number_format($metrics['total_sales_value'], 2)); ?></td></tr>
        <tr><td><?php echo e(__('Refunds')); ?></td><td class="right"><?php echo e($metrics['refunds']); ?></td></tr>
    </table>

    <h2><?php echo e(__('Payment summary')); ?></h2>
    <table>
        <tr><td><?php echo e(__('Cash')); ?></td><td class="right"><?php echo e(number_format($metrics['cash_sales'], 2)); ?></td></tr>
        <tr><td><?php echo e(__('M-Pesa')); ?></td><td class="right"><?php echo e(number_format($metrics['mpesa_sales'], 2)); ?></td></tr>
        <tr><td><?php echo e(__('Card')); ?></td><td class="right"><?php echo e(number_format($metrics['card_sales'], 2)); ?></td></tr>
        <tr><td><?php echo e(__('Bank')); ?></td><td class="right"><?php echo e(number_format($metrics['bank_sales'], 2)); ?></td></tr>
        <tr><td><strong><?php echo e(__('Expected total')); ?></strong></td><td class="right"><strong><?php echo e(number_format($metrics['expected_total'], 2)); ?></strong></td></tr>
    </table>

    <h2><?php echo e(__('Variance')); ?></h2>
    <table>
        <tr><td><?php echo e(__('Expected cash')); ?></td><td class="right"><?php echo e(number_format($session->expected_cash ?? $metrics['expected_closing_cash'], 2)); ?></td></tr>
        <tr><td><?php echo e(__('Actual cash')); ?></td><td class="right"><?php echo e($session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—'); ?></td></tr>
        <tr><td><?php echo e(__('Variance')); ?></td><td class="right"><?php echo e($session->variance !== null ? number_format($session->variance, 2) : '—'); ?></td></tr>
        <tr><td><?php echo e(__('Tolerance')); ?></td><td class="right"><?php echo e(number_format($varianceTolerance, 2)); ?></td></tr>
    </table>

    <?php if($session->varianceApprover): ?>
        <p><?php echo e(__('Approved by :name on :date', ['name' => $session->varianceApprover->name, 'date' => $session->variance_approved_at?->format('Y-m-d H:i')])); ?></p>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\sessions\exports\summary-pdf.blade.php ENDPATH**/ ?>