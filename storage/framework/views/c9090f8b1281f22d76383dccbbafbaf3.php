<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($sheet['job_number']); ?> — <?php echo e(__('Job sheet')); ?></title>
    <style>
        @page { margin: 10mm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 16px;
            font-size: 11px;
            line-height: 1.35;
        }
        .sheet { max-width: 210mm; margin: 0 auto; border: 2px solid #1e3a8a; }
        .sheet__header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            padding: 12px 14px 8px;
            border-bottom: 2px solid #1e3a8a;
        }
        .brand { font-size: 22px; font-weight: 800; color: #db2777; letter-spacing: 0.02em; }
        .brand small { display: block; font-size: 10px; font-weight: 700; color: #1e3a8a; letter-spacing: 0.08em; }
        .title { font-size: 28px; font-weight: 800; color: #db2777; text-align: right; align-self: center; }
        .contact { grid-column: 1 / -1; font-size: 9px; color: #334155; border-top: 1px solid #cbd5e1; padding-top: 6px; }
        .meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 10px 14px;
            border-bottom: 2px solid #1e3a8a;
        }
        .meta__label { font-weight: 700; color: #1e3a8a; font-size: 10px; text-transform: uppercase; }
        .meta__value { border-bottom: 1px solid #64748b; min-height: 18px; margin-top: 2px; font-size: 12px; }
        .section-title {
            background: #db2777;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 5px 8px;
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #1e3a8a; padding: 5px 6px; vertical-align: top; }
        th { background: #eff6ff; color: #1e3a8a; font-size: 9px; text-transform: uppercase; }
        .ncr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
        .ncr-grid span { display: block; border-top: 1px solid #1e3a8a; padding: 4px 6px; min-height: 22px; }
        .ncr-grid small { display: block; font-size: 8px; font-weight: 700; color: #1e3a8a; }
        .notes {
            min-height: 56px;
            padding: 8px 14px;
            border-bottom: 2px solid #1e3a8a;
            white-space: pre-wrap;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 12px 14px;
            border-bottom: 2px solid #1e3a8a;
        }
        .sign-line { border-bottom: 1px solid #64748b; min-height: 22px; margin-top: 18px; }
        .checks {
            display: flex;
            justify-content: space-around;
            padding: 10px 14px 14px;
            font-size: 11px;
            font-weight: 700;
        }
        .check { display: inline-flex; align-items: center; gap: 6px; }
        .box {
            width: 14px;
            height: 14px;
            border: 2px solid #1e3a8a;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <p class="no-print" style="text-align:center;margin-bottom:12px;">
        <button type="button" onclick="window.print()" style="padding:8px 16px;cursor:pointer;"><?php echo e(__('Print')); ?></button>
    </p>

    <div class="sheet">
        <div class="sheet__header">
            <div>
                <div class="brand">
                    <?php echo e($sheet['company_name']); ?>

                    <small><?php echo e(__('Printing & Branding')); ?></small>
                </div>
            </div>
            <div class="title"><?php echo e(__('Job Sheet')); ?></div>
            <div class="contact">
                <?php if($sheet['company_address']): ?><?php echo e($sheet['company_address']); ?> · <?php endif; ?>
                <?php if($sheet['company_phone']): ?><?php echo e(__('Tel')); ?>: <?php echo e($sheet['company_phone']); ?> · <?php endif; ?>
                <?php if($sheet['company_email']): ?><?php echo e($sheet['company_email']); ?><?php endif; ?>
            </div>
        </div>

        <div class="meta">
            <div>
                <div class="meta__label"><?php echo e(__('No.')); ?></div>
                <div class="meta__value"><?php echo e($sheet['job_number']); ?></div>
            </div>
            <div>
                <div class="meta__label"><?php echo e(__('Date')); ?></div>
                <div class="meta__value"><?php echo e($sheet['date']); ?></div>
            </div>
            <div>
                <div class="meta__label"><?php echo e(__('Ms')); ?></div>
                <div class="meta__value"><?php echo e($sheet['customer_name']); ?></div>
            </div>
        </div>

        <div class="section-title"><?php echo e(__('Printing specifications')); ?></div>
        <table>
            <thead>
                <tr>
                    <th style="width:8%"><?php echo e(__('Qty')); ?></th>
                    <th style="width:22%"><?php echo e(__('Description')); ?></th>
                    <th style="width:24%"><?php echo e(__('Paper colour')); ?></th>
                    <th style="width:18%"><?php echo e(__('Paper stock')); ?></th>
                    <th style="width:12%"><?php echo e(__('Ink')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $sheet['printing_rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['quantity']); ?></td>
                        <td><?php echo e($row['description']); ?></td>
                        <td>
                            <table style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small><?php echo e(__('ORIG')); ?></small><br><?php echo e($row['orig']); ?></td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small><?php echo e(__('DUP')); ?></small><br><?php echo e($row['dup']); ?></td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small><?php echo e(__('TRI')); ?></small><br><?php echo e($row['tri']); ?></td>
                                    <td style="border:1px solid #1e3a8a;padding:3px 4px;"><small><?php echo e(__('QUAD')); ?></small><br><?php echo e($row['quad']); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td><?php echo e($row['paper_stock']); ?></td>
                        <td><?php echo e($row['ink']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="section-title"><?php echo e(__('Binding specifications')); ?></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('Number')); ?></th>
                    <th><?php echo e(__('Pages / pad')); ?></th>
                    <th><?php echo e(__('Size')); ?></th>
                    <th><?php echo e(__('No. of ups')); ?></th>
                    <th><?php echo e(__('Binding')); ?></th>
                    <th><?php echo e(__('Date of collection')); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo e($sheet['binding']['serial_start']); ?></td>
                    <td><?php echo e($sheet['binding']['pages_per_pad']); ?></td>
                    <td><?php echo e($sheet['binding']['size']); ?></td>
                    <td><?php echo e($sheet['binding']['ups']); ?></td>
                    <td><?php echo e($sheet['binding']['binding']); ?></td>
                    <td><?php echo e($sheet['binding']['collection_date']); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="section-title"><?php echo e(__('Note')); ?></div>
        <div class="notes"><?php echo e($sheet['notes']); ?></div>

        <div class="section-title"><?php echo e(__('Material requisition')); ?></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('Paper type')); ?></th>
                    <th><?php echo e(__('No. of sheets A4 / A3')); ?></th>
                    <th><?php echo e(__('No. of sheets A1')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $sheet['material_rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['paper_type']); ?></td>
                        <td><?php echo e($row['sheets_a4_a3']); ?></td>
                        <td><?php echo e($row['sheets_a1']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="signatures">
            <div>
                <strong><?php echo e(__('Prepared by')); ?></strong>
                <div class="sign-line"><?php echo e($sheet['prepared_by']); ?></div>
                <small><?php echo e(__('Sign')); ?></small>
            </div>
            <div>
                <strong><?php echo e(__('Store')); ?></strong>
                <div class="sign-line"></div>
                <small><?php echo e(__('Sign')); ?></small>
            </div>
        </div>

        <div class="checks">
            <span class="check"><span class="box"><?php if($sheet['status']['printed']): ?>✓<?php endif; ?></span> <?php echo e(__('Printed')); ?></span>
            <span class="check"><span class="box"><?php if($sheet['status']['complete']): ?>✓<?php endif; ?></span> <?php echo e(__('Complete')); ?></span>
            <span class="check"><span class="box"><?php if($sheet['status']['collected']): ?>✓<?php endif; ?></span> <?php echo e(__('Collected')); ?></span>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/job-sheet.blade.php ENDPATH**/ ?>