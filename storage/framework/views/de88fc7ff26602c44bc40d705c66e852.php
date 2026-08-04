<div class="sheet">
    <div class="sheet__header">
        <div class="brand-wrap">
            <img src="<?php echo e(asset('images/logo-sidebar.png')); ?>" alt="" class="brand-logo">
            <div class="brand">
                <?php echo e($sheet['company_name']); ?>

                <small><?php echo e(__('Printing & Branding')); ?></small>
            </div>
        </div>
        <div class="title"><?php echo e(__('Job Sheet')); ?></div>
        <div class="contact">
            <?php if($sheet['company_address']): ?><?php echo e($sheet['company_address']); ?><br><?php endif; ?>
            <?php if($sheet['company_phone']): ?><?php echo e(__('Tel')); ?>: <?php echo e($sheet['company_phone']); ?><?php endif; ?>
            <?php if($sheet['company_phone'] && $sheet['company_email']): ?> · <?php endif; ?>
            <?php if($sheet['company_email']): ?><?php echo e(__('Email')); ?>: <?php echo e($sheet['company_email']); ?><?php endif; ?>
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
        <div class="meta__customer">
            <div class="meta__label"><?php echo e(__('Ms')); ?></div>
            <div class="meta__value"><?php echo e($sheet['customer_name']); ?></div>
        </div>
    </div>

    <div class="section-title"><?php echo e(__('Printing specifications')); ?></div>
    <table>
        <thead>
            <tr>
                <th style="width:8%"><?php echo e(__('Qty')); ?></th>
                <th style="width:24%"><?php echo e(__('Description')); ?></th>
                <th style="width:28%"><?php echo e(__('Paper colour')); ?></th>
                <th style="width:20%"><?php echo e(__('Paper stock')); ?></th>
                <th style="width:12%"><?php echo e(__('Ink')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $sheet['printing_rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($row['quantity']); ?></td>
                    <td><?php echo e($row['description']); ?></td>
                    <td class="ncr-cell">
                        <table class="ncr-inner">
                            <tr>
                                <td><small><?php echo e(__('ORIG')); ?></small><?php echo e($row['orig']); ?></td>
                                <td><small><?php echo e(__('DUP')); ?></small><?php echo e($row['dup']); ?></td>
                                <td><small><?php echo e(__('TRI')); ?></small><?php echo e($row['tri']); ?></td>
                                <td><small><?php echo e(__('QUAD')); ?></small><?php echo e($row['quad']); ?></td>
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

    <div class="notes">
        <div class="notes__label"><?php echo e(__('Note')); ?>:-</div>
        <?php echo e($sheet['notes']); ?>

    </div>

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
            <strong><?php echo e(__('Prepared by')); ?>:</strong>
            <div class="sign-line"><?php echo e($sheet['prepared_by']); ?></div>
            <small><?php echo e(__('Sign')); ?>:</small>
        </div>
        <div>
            <strong><?php echo e(__('Store')); ?>:</strong>
            <div class="sign-line"></div>
            <small><?php echo e(__('Sign')); ?>:</small>
        </div>
    </div>

    <div class="checks">
        <span class="check"><span class="box"><?php if($sheet['status']['printed']): ?>✓<?php endif; ?></span> <?php echo e(__('Printed')); ?></span>
        <span class="check"><span class="box"><?php if($sheet['status']['complete']): ?>✓<?php endif; ?></span> <?php echo e(__('Complete')); ?></span>
        <span class="check"><span class="box"><?php if($sheet['status']['collected']): ?>✓<?php endif; ?></span> <?php echo e(__('Collected')); ?></span>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\partials\job-sheet-body.blade.php ENDPATH**/ ?>