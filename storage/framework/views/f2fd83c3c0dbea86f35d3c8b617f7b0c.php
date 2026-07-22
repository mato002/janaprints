<div class="crm-360__tab-stack">
    <?php if(! empty($acquisition['attachments'])): ?>
        <section class="crm-360__card">
            <h2 class="crm-360__card-title"><?php echo e(__('Storefront artwork')); ?></h2>
            <ul class="space-y-2 text-sm">
                <?php $__currentLoopData = $acquisition['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <span class="font-medium"><?php echo e($attachment['name']); ?></span>
                        <?php if(! empty($attachment['preview_url'])): ?>
                            <a href="<?php echo e($attachment['preview_url']); ?>" class="text-erp-accent hover:underline" target="_blank" rel="noopener"><?php echo e(__('Preview')); ?></a>
                        <?php endif; ?>
                        <?php if(! empty($attachment['download_url'])): ?>
                            <a href="<?php echo e($attachment['download_url']); ?>" class="text-erp-accent hover:underline"><?php echo e(__('Download')); ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Artwork from linked quotations')); ?></h2>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('artwork.view')): ?>
            <table class="erp-table text-sm w-full">
                <thead>
                    <tr>
                        <th><?php echo e(__('Request')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $artwork; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item['number']); ?></td>
                            <td><?php echo e($item['status']); ?></td>
                            <td><?php echo e($item['date']?->format('d M Y')); ?></td>
                            <td>
                                <a href="<?php echo e($item['url']); ?>" class="text-erp-accent hover:underline text-sm" data-turbo-frame="erp-main"><?php echo e(__('View')); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-slate-500 py-4"><?php echo e(__('No artwork requests linked via quotations yet')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="crm-360__empty-inline"><?php echo e(__('You do not have permission to view artwork')); ?></p>
        <?php endif; ?>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-artwork.blade.php ENDPATH**/ ?>