<section class="qr-360__card">
    <h2 class="qr-360__card-title"><?php echo e(__('Internal Collaboration')); ?></h2>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quoteRequest)): ?>
        <form method="POST" action="<?php echo e(route('admin.public-quote-requests.notes.store', $quoteRequest)); ?>" class="mb-4">
            <?php echo csrf_field(); ?>
            <label class="qr-360__label" for="qr-note-body"><?php echo e(__('Add Note')); ?></label>
            <textarea
                id="qr-note-body"
                name="body"
                class="erp-input mt-1 w-full min-h-[4.5rem] text-sm"
                rows="3"
                placeholder="<?php echo e(__('Add an internal note for the commercial team…')); ?>"
                required
            ><?php echo e(old('body')); ?></textarea>
            <div class="mt-2">
                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm"><?php echo e(__('Save Note')); ?></button>
            </div>
        </form>
    <?php endif; ?>

    <ul class="crm-360__notes-feed" role="list">
        <?php $__empty_1 = true; $__currentLoopData = $workspace['notes_feed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="crm-360__note-card">
                <div class="crm-360__note-head">
                    <span class="crm-360__note-author"><?php echo e($note['author']); ?></span>
                    <time class="crm-360__note-time"><?php echo e($note['at']?->format('M j, Y g:i A')); ?> · <?php echo e($note['at']?->diffForHumans()); ?></time>
                </div>
                <p class="crm-360__note-body whitespace-pre-wrap"><?php echo e($note['body']); ?></p>
                <?php if($note['legacy']): ?>
                    <p class="mt-2 text-[11px] text-slate-500"><?php echo e(__('Imported from legacy notes field')); ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="crm-360__empty-inline"><?php echo e(__('No internal notes yet')); ?></li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\collaboration.blade.php ENDPATH**/ ?>