<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Artwork'),'heading' => __('Artwork')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork'))]); ?>
    <section class="client-panel client-panel--flush">
        <div class="client-panel__head">
            <div class="client-panel__title-wrap">
                <span class="client-panel__icon"><?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'palette','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'palette','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?></span>
                <h2 class="client-panel__title"><?php echo e(__('Upload artwork')); ?></h2>
            </div>
        </div>
        <form method="POST" action="<?php echo e(route('client.artwork-library.store')); ?>" enctype="multipart/form-data" class="client-upload-form">
            <?php echo csrf_field(); ?>
            <div class="client-upload-form__grid">
                <div>
                    <label for="artwork_name" class="client-label"><?php echo e(__('Name')); ?></label>
                    <input id="artwork_name" type="text" name="artwork_name" class="client-input" value="<?php echo e(old('artwork_name')); ?>" required>
                </div>
                <div>
                    <label for="file" class="client-label"><?php echo e(__('File')); ?></label>
                    <input id="file" type="file" name="file" class="client-input" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                </div>
            </div>
            <button type="submit" class="client-btn"><?php echo e(__('Upload')); ?></button>
        </form>
    </section>

    <section class="client-panel client-panel--flush">
        <div class="client-panel__head">
            <div class="client-panel__title-wrap">
                <h2 class="client-panel__title"><?php echo e(__('Your files')); ?></h2>
            </div>
        </div>
        <div class="client-table-wrap">
            <table class="client-table">
                <thead>
                    <tr>
                        <th><?php echo e(__('Artwork')); ?></th>
                        <th><?php echo e(__('Version')); ?></th>
                        <th><?php echo e(__('Uploaded')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $libraryArtworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($artwork->artwork_name); ?></td>
                            <td><?php echo e($artwork->versionLabel()); ?></td>
                            <td><?php echo e($artwork->uploaded_at?->format('M j, Y') ?: '—'); ?></td>
                            <td class="client-table__actions">
                                <?php if($artwork->isPreviewable()): ?>
                                    <a href="<?php echo e(route('client.artwork-library.preview', $artwork)); ?>" target="_blank" rel="noopener" class="client-link"><?php echo e(__('View')); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="client-empty"><?php echo e(__('No files yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if($requests->isNotEmpty()): ?>
        <section class="client-panel client-panel--flush">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <h2 class="client-panel__title"><?php echo e(__('Artwork requests')); ?></h2>
                </div>
            </div>
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Request')); ?></th>
                            <th><?php echo e(__('Title')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($request->request_number); ?></td>
                                <td><?php echo e($request->title); ?></td>
                                <td><?php echo $__env->make('client.partials.status-badge', ['status' => $request->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                <td><a href="<?php echo e(route('client.artwork.show', $request)); ?>" class="client-link"><?php echo e(__('Open')); ?></a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($requests->links()); ?>

        </section>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\artwork\index.blade.php ENDPATH**/ ?>