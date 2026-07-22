<div class="border-t border-erp-border bg-slate-50 px-4 py-2">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-xs font-semibold uppercase text-slate-600"><?php echo e(__('Attachments')); ?></h3>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
            <form method="POST" action="<?php echo e(route('admin.communications.inbox.attachments.store', $active)); ?>" enctype="multipart/form-data" class="flex gap-1">
                <?php echo csrf_field(); ?>
                <select name="attachment_type" class="erp-input text-xs">
                    <option value="image"><?php echo e(__('Image')); ?></option>
                    <option value="pdf"><?php echo e(__('PDF')); ?></option>
                    <option value="artwork"><?php echo e(__('Artwork')); ?></option>
                    <option value="quotation"><?php echo e(__('Quotation')); ?></option>
                    <option value="invoice"><?php echo e(__('Invoice')); ?></option>
                    <option value="proof"><?php echo e(__('Proof')); ?></option>
                </select>
                <input type="file" name="file" class="text-xs max-w-[8rem]">
                <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Upload')); ?></button>
            </form>
        <?php endif; ?>
    </div>
    <?php if($active->attachments->isNotEmpty()): ?>
        <ul class="mt-2 flex flex-wrap gap-2 text-xs">
            <?php $__currentLoopData = $active->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="rounded border border-erp-border bg-white px-2 py-1 flex items-center gap-2">
                    <span><?php echo e($att->label ?? __('File')); ?></span>
                    <span class="text-slate-400"><?php echo e($att->attachment_type); ?></span>
                    <?php if($att->file_path): ?>
                        <a href="<?php echo e(route('admin.communications.inbox.attachments.download', [$active, $att])); ?>" class="text-erp-accent hover:underline"><?php echo e(__('Download')); ?></a>
                        <?php if(str_starts_with((string) $att->attachment_type, 'image') || str_contains($att->file_path, '.jpg') || str_contains($att->file_path, '.png')): ?>
                            <a href="<?php echo e(asset('storage/'.$att->file_path)); ?>" target="_blank" class="text-erp-accent"><?php echo e(__('Preview')); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($att->attachable_type): ?>
                        <span class="text-slate-500"><?php echo e(__('Linked record')); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\attachments-center.blade.php ENDPATH**/ ?>