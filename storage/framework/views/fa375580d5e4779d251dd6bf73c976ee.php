<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notes', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
    <details class="border-t border-erp-border bg-amber-50/50 lg:hidden">
        <summary class="cursor-pointer px-4 py-2 text-xs font-semibold text-amber-900"><?php echo e(__('Internal notes timeline')); ?></summary>
        <ul class="max-h-48 space-y-2 overflow-y-auto px-4 pb-3 text-xs">
            <?php $__empty_1 = true; $__currentLoopData = $active->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="rounded border border-amber-200 bg-white p-2">
                    <p class="font-semibold text-amber-800"><?php echo e($note->author?->name); ?> · <?php echo e($note->created_at->format('d M H:i')); ?></p>
                    <p class="mt-1 whitespace-pre-wrap text-slate-800"><?php echo e($note->body); ?></p>
                    <?php if(! empty($note->tags)): ?>
                        <p class="mt-1"><?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="text-amber-700">#<?php echo e($t); ?></span> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="text-slate-500"><?php echo e(__('No internal notes yet.')); ?></li>
            <?php endif; ?>
        </ul>
    </details>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\notes-drawer.blade.php ENDPATH**/ ?>