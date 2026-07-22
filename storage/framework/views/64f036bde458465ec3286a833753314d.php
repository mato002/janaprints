<?php
    $media = $workspaceData['media_library'] ?? ['images' => 0, 'files' => 0, 'items' => collect(), 'by_month' => collect()];
    $photos = collect($media['items'] ?? [])->filter(fn ($i) => $i['is_image']);
    $docs = collect($media['items'] ?? [])->reject(fn ($i) => $i['is_image']);
    $photosByMonth = $photos->groupBy('month_key');
?>

<div class="text-xs" x-data="{ mediaTab: 'photos' }">
    <div class="mb-2 flex gap-1 border-b border-erp-border">
        <button type="button" @click="mediaTab='photos'" class="px-2 py-1.5 font-semibold uppercase text-[9px]" :class="mediaTab==='photos' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-500'">
            <?php echo e(__('Photos')); ?> (<?php echo e($media['images']); ?>)
        </button>
        <button type="button" @click="mediaTab='files'" class="px-2 py-1.5 font-semibold uppercase text-[9px]" :class="mediaTab==='files' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-500'">
            <?php echo e(__('Files')); ?> (<?php echo e($media['files']); ?>)
        </button>
    </div>

    <p class="mb-2 text-[11px] text-slate-500"><?php echo e(__('Grouped by date — tap to jump to that message in the chat.')); ?></p>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.attachments.store', $active)); ?>" enctype="multipart/form-data" class="mb-3 flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <input type="file" name="file" class="erp-input min-w-0 flex-1 text-[10px]" accept="image/*,.pdf">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs shrink-0"><?php echo e(__('Upload')); ?></button>
        </form>
    <?php endif; ?>

    <div x-show="mediaTab==='photos'" x-cloak class="max-h-[50vh] space-y-3 overflow-y-auto">
        <?php $__empty_1 = true; $__currentLoopData = $photosByMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthKey => $monthItems): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div>
                <p class="mb-1 font-semibold text-slate-600"><?php echo e($monthItems->first()['month_label'] ?? $monthKey); ?></p>
                <div class="grid grid-cols-3 gap-1">
                    <?php $__currentLoopData = $monthItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button
                            type="button"
                            class="relative aspect-square overflow-hidden rounded-md bg-slate-100"
                            @click="$dispatch('open-chat-item', '<?php echo e($item['dom_id']); ?>')"
                            title="<?php echo e($item['at']->format('d M Y H:i')); ?>"
                        >
                            <img src="<?php echo e($item['file_url']); ?>" alt="" class="h-full w-full object-cover" loading="lazy">
                            <span class="absolute bottom-0 left-0 right-0 bg-black/50 px-1 py-0.5 text-[9px] text-white"><?php echo e($item['at']->format('d M')); ?></span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No photos sent yet.')); ?></p>
        <?php endif; ?>
    </div>

    <div x-show="mediaTab==='files'" x-cloak class="max-h-[50vh] space-y-2 overflow-y-auto">
        <?php $__empty_1 = true; $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-lg border border-erp-border bg-white p-2">
                <p class="font-medium text-erp-primary"><?php echo e($item['label']); ?></p>
                <p class="text-slate-400"><?php echo e($item['at']->format('d M Y, H:i')); ?></p>
                <div class="mt-1 flex flex-wrap gap-2">
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'xs','@click' => '$dispatch(\'open-chat-item\', \''.e($item['dom_id']).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'xs','@click' => '$dispatch(\'open-chat-item\', \''.e($item['dom_id']).'\')']); ?><?php echo e(__('View in chat')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'xs','href' => $item['download_url']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'xs','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['download_url'])]); ?><?php echo e(__('Download')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                        <form method="POST" action="<?php echo e(route('admin.communications.inbox.attachments.destroy', [$active, $item['id']])); ?>" class="inline" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Remove this file?'))->toHtml() ?>)">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline"><?php echo e(__('Delete')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No documents yet.')); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\attachments-hub.blade.php ENDPATH**/ ?>