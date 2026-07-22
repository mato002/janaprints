<?php
    $files = $workspace['artwork_files'];
    $active = $files[0] ?? null;
?>

<section class="qr-360__card qr-360__card--artwork">
    <div class="qr-360__card-head">
        <h2 class="qr-360__card-title"><?php echo e(__('Artwork Review')); ?></h2>
        <?php if($active): ?>
            <div class="flex flex-wrap gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.analyze')): ?>
                    <?php if($active['pi_supported'] ?? false): ?>
                        <button
                            type="button"
                            class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"
                            x-show="piSummary"
                            x-cloak
                            @click="openPiModal()"
                        >
                            <?php echo e(__('View Analysis')); ?>

                        </button>
                        <form
                            method="POST"
                            class="inline"
                            :action="piSummary ? piRerunUrl : piRunUrl"
                            @submit.prevent="submitPiForm($event)"
                        >
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" :disabled="piAnalysisLoading">
                                <span x-show="! piSummary"><?php echo e(__('Run Printing Intelligence Analysis')); ?></span>
                                <span x-show="piSummary" x-cloak><?php echo e(__('Re-run Analysis')); ?></span>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <button type="button" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" @click="artworkOpen = true"><?php echo e(__('Expand')); ?></button>
                <a href="<?php echo e($active['download_url']); ?>" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"><?php echo e(__('Download')); ?></a>
            </div>
        <?php endif; ?>
    </div>

    <?php if($active): ?>
        <div class="qr-360__artwork">
            <div class="qr-360__artwork-stage">
                <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div <?php if(count($files) > 1): ?> x-show="activeArtwork === <?php echo \Illuminate\Support\Js::from($file['id'])->toHtml() ?>" <?php endif; ?>>
                        <?php if($file['is_image']): ?>
                            <img
                                src="<?php echo e($file['preview_url']); ?>"
                                alt="<?php echo e($file['name']); ?>"
                                class="qr-360__artwork-image"
                                loading="eager"
                            >
                        <?php elseif($file['is_pdf']): ?>
                            <iframe
                                src="<?php echo e($file['preview_url']); ?>"
                                title="<?php echo e($file['name']); ?>"
                                class="qr-360__artwork-pdf"
                            ></iframe>
                        <?php else: ?>
                            <div class="qr-360__artwork-file">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'document-text','class' => 'h-12 w-12 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document-text','class' => 'h-12 w-12 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                                <p class="mt-3 text-sm font-semibold text-slate-700"><?php echo e($file['name']); ?></p>
                                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500"><?php echo e($file['extension']); ?> <?php echo e(__('file')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if(count($files) > 1): ?>
                <div class="qr-360__artwork-thumbs">
                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button
                            type="button"
                            class="qr-360__artwork-thumb"
                            :class="activeArtwork === <?php echo \Illuminate\Support\Js::from($file['id'])->toHtml() ?> && 'qr-360__artwork-thumb--active'"
                            @click="activeArtwork = <?php echo \Illuminate\Support\Js::from($file['id'])->toHtml() ?>"
                        >
                            <?php if($file['is_image']): ?>
                                <img src="<?php echo e($file['preview_url']); ?>" alt="" class="h-full w-full object-cover">
                            <?php else: ?>
                                <span class="text-[10px] font-bold uppercase"><?php echo e($file['extension']); ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <div class="qr-360__artwork-meta">
                <p class="font-medium text-slate-800"><?php echo e($active['name']); ?></p>
                <p class="text-xs text-slate-500">
                    <?php echo e(strtoupper($active['extension'])); ?>

                    · <?php echo e(number_format($active['size'] / 1024, 1)); ?> KB
                    · <?php echo e(__('Uploaded')); ?> <?php echo e($active['uploaded_at']->format('d M Y, H:i')); ?>

                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="qr-360__artwork-empty">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'color-swatch','class' => 'h-10 w-10 text-slate-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'color-swatch','class' => 'h-10 w-10 text-slate-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
            <p class="mt-3 text-sm font-medium text-slate-600"><?php echo e(__('No artwork uploaded')); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Customer did not attach artwork with this request.')); ?></p>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\artwork.blade.php ENDPATH**/ ?>