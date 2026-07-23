<?php if(! empty($meta)): ?>
    <table class="jp-doc__commercial-meta" cellpadding="0" cellspacing="0">
        <?php if(! empty($stacked)): ?>
            <tr>
                <td class="jp-doc__commercial-meta-col" style="width: 100%; padding-right: 0;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        <?php $__currentLoopData = $meta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="jp-doc__commercial-meta-label"><?php echo e($row['label']); ?></td>
                                <td class="jp-doc__commercial-meta-value"><?php echo e($row['value']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </table>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <?php $__currentLoopData = array_chunk($meta, (int) ceil(count($meta) / 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="jp-doc__commercial-meta-col">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;">
                            <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="jp-doc__commercial-meta-label"><?php echo e($row['label']); ?></td>
                                    <td class="jp-doc__commercial-meta-value"><?php echo e($row['value']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </table>
                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        <?php endif; ?>
    </table>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/documents/partials/commercial-meta.blade.php ENDPATH**/ ?>