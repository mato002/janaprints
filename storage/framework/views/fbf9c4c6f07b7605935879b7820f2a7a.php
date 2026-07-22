<div class="rounded border border-erp-border/60 bg-white p-2">
    <h4 class="text-xs font-semibold uppercase text-slate-600"><?php echo e(__('Conversation tags')); ?></h4>
    <form method="POST" action="<?php echo e(route('admin.communications.inbox.tags.update', $active)); ?>" class="mt-2 space-y-2" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
        <?php echo csrf_field(); ?>
        <?php if($channelFilter): ?><input type="hidden" name="channel" value="<?php echo e($channelFilter); ?>"><?php endif; ?>
        <input type="text" name="tags" value="<?php echo e(implode(', ', $active->tags ?? [])); ?>" class="erp-input w-full text-xs" placeholder="<?php echo e(__('urgent, vip, artwork')); ?>">
        <div class="flex flex-wrap gap-1">
            <?php $__currentLoopData = $workspaceData['suggested_tags'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600 hover:bg-erp-accent/10"
                        onclick="const i=this.closest('form').querySelector('[name=tags]'); i.value = i.value ? i.value+', <?php echo e($tag); ?>' : '<?php echo e($tag); ?>'">#<?php echo e($tag); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs w-full"><?php echo e(__('Save tags')); ?></button>
    </form>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tags-panel.blade.php ENDPATH**/ ?>