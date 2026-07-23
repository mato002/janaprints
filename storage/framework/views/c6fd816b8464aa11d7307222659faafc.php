<?php if(! empty($workspace['artwork_files'])): ?>
    <div
        x-show="artworkOpen"
        x-cloak
        class="qr-360__artwork-modal"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="artworkOpen = false"
    >
        <div class="qr-360__artwork-modal-backdrop" @click="artworkOpen = false"></div>
        <div class="qr-360__artwork-modal-panel">
            <?php
                $modalFile = $workspace['artwork_files'][0];
            ?>
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white"><?php echo e($modalFile['name']); ?></h3>
                <button type="button" class="crm-360__btn crm-360__btn--ghost text-white" @click="artworkOpen = false"><?php echo e(__('Close')); ?></button>
            </div>
            <?php $__currentLoopData = $workspace['artwork_files']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div x-show="activeArtwork === <?php echo \Illuminate\Support\Js::from($file['id'])->toHtml() ?>" x-cloak>
                    <?php if($file['is_image']): ?>
                        <img src="<?php echo e($file['preview_url']); ?>" alt="<?php echo e($file['name']); ?>" class="max-h-[80vh] w-full rounded-xl object-contain">
                    <?php elseif($file['is_pdf']): ?>
                        <iframe src="<?php echo e($file['preview_url']); ?>" title="<?php echo e($file['name']); ?>" class="h-[80vh] w-full rounded-xl bg-white"></iframe>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/customer-service/quote-requests/workspace/artwork-modal.blade.php ENDPATH**/ ?>