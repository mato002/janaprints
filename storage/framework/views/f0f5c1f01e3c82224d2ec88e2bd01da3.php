<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo e($label['job_number']); ?> — <?php echo e(__('Scan label')); ?></title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; }
        .label { border: 2px solid #111; padding: 20px; width: 320px; margin: 0 auto; text-align: center; }
        .customer { font-size: 13px; color: #334155; margin-bottom: 8px; }
        .code { font-size: 26px; font-weight: 700; letter-spacing: 3px; margin: 10px 0; font-family: ui-monospace, monospace; }
        .meta { font-size: 11px; color: #64748b; margin-top: 8px; }
        .qr { margin: 12px auto; max-width: 160px; }
        .qr svg { width: 100%; height: auto; }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <div class="customer"><?php echo e($label['customer'] ?? '—'); ?></div>
        <div class="code"><?php echo e($label['barcode']); ?></div>
        <div class="qr"><?php echo $label['qr_svg']; ?></div>
        <div class="meta">
            <?php if($label['sales_order']): ?>
                <?php echo e(__('Order')); ?>: <?php echo e($label['sales_order']); ?> ·
            <?php endif; ?>
            <?php if($label['department_label']): ?>
                <?php echo e($label['department_label']); ?>

            <?php endif; ?>
        </div>
        <div class="meta"><?php echo e($label['scan_url']); ?></div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\scan-label.blade.php ENDPATH**/ ?>