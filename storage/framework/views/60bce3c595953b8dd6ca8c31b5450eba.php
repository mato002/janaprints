<?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $type = $event['type'] ?? 'message';
        $isOutgoing = $type === 'message' && ($event['direction'] ?? '') === 'outgoing';
        $isIncoming = $type === 'message' && ($event['direction'] ?? '') === 'incoming';
        $isNote = $type === 'internal_note';
        $isErp = $type === 'erp';
        $isSystem = in_array($type, ['system', 'audit'], true);
        $channel = $event['channel'] ?? null;
        $channelEnum = $channel ? \App\Enums\InboxMessageChannel::tryFrom($channel) : null;
    ?>
    <div class="mb-3 flex <?php echo e($isOutgoing ? 'justify-end' : ($isSystem || $isErp ? 'justify-center' : 'justify-start')); ?>">
        <?php if($isSystem || $isErp): ?>
            <div class="max-w-[92%] rounded-lg border border-slate-200 bg-white/90 px-3 py-2 text-center text-xs text-slate-600 shadow-sm">
                <p class="font-semibold text-slate-700"><?php echo e($event['at']->format('d M H:i')); ?> · <?php echo e($event['title']); ?></p>
                <p class="mt-0.5">
                    <?php if(! empty($event['url'])): ?>
                        <a href="<?php echo e($event['url']); ?>" class="text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($event['body']); ?></a>
                    <?php else: ?>
                        <?php echo e($event['body']); ?>

                    <?php endif; ?>
                </p>
                <?php if(! empty($event['meta'])): ?><p class="text-[10px] text-slate-400"><?php echo e($event['meta']); ?></p><?php endif; ?>
            </div>
        <?php elseif($isNote): ?>
            <div class="max-w-[88%] rounded-lg border-2 border-dashed border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-950 shadow-sm">
                <p class="flex items-center gap-1 text-[10px] font-bold uppercase text-amber-800">
                    <span class="rounded bg-amber-200 px-1"><?php echo e(__('Internal')); ?></span>
                    <?php echo e($event['at']->format('H:i')); ?>

                </p>
                <p class="mt-1 whitespace-pre-wrap"><?php echo e($event['body']); ?></p>
                <?php if(! empty($event['tags'])): ?>
                    <p class="mt-1 flex flex-wrap gap-1">
                        <?php $__currentLoopData = $event['tags']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="rounded bg-amber-200/80 px-1 text-[10px]">#<?php echo e($tag); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </p>
                <?php endif; ?>
                <?php if(! empty($event['meta'])): ?><p class="mt-1 text-[10px] text-amber-700"><?php echo e($event['meta']); ?></p><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'max-w-[75%] px-3 py-1.5 text-sm shadow-sm',
                'rounded-lg rounded-br-none bg-[#d9fdd3] text-slate-900' => $isOutgoing,
                'rounded-lg rounded-bl-none bg-white text-slate-900' => $isIncoming,
            ]); ?>">
                <p class="whitespace-pre-wrap"><?php echo e($event['body']); ?></p>
                <p class="mt-0.5 text-right text-[11px] text-slate-500"><?php echo e($event['at']->format('H:i')); ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="py-12 text-center text-sm text-slate-500"><?php echo e(__('No activity yet. Send a message or add an internal note.')); ?></p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\timeline-feed.blade.php ENDPATH**/ ?>