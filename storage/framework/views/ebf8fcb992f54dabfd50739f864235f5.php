<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Artwork Analysis'),'breadcrumbs' => [
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Artwork Analysis')],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Artwork Analysis'),'description' => __('Upload artwork, extract metadata (PI1), and analyse colour coverage (PI2). No cost estimation or AI.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork Analysis')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Upload artwork, extract metadata (PI1), and analyse colour coverage (PI2). No cost estimation or AI.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php echo $__env->make('admin.printing-intelligence.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('admin.printing-intelligence.partials.environment-warnings', ['environment' => $environment ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.analyze')): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1']); ?>
                <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Upload artwork')); ?></h3>
                <form method="POST" action="<?php echo e(route('admin.printing-intelligence.artwork-analysis.upload')); ?>" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="file"><?php echo e(__('Artwork file')); ?></label>
                        <input type="file" name="file" id="file" required
                               accept="<?php echo e(implode(',', array_map(fn ($ext) => '.'.$ext, $config['allowed_artwork_extensions'] ?? []))); ?>"
                               class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white">
                        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <p class="text-xs text-slate-500">
                        <?php echo e(__('Accepted: :types. Max :size MB.', [
                            'types' => strtoupper(implode(', ', $config['allowed_artwork_extensions'] ?? [])),
                            'size' => $config['max_artwork_upload_mb'] ?? 50,
                        ])); ?>

                    </p>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Analyze artwork')); ?></button>
                </form>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => \Illuminate\Support\Arr::toCssClasses(['lg:col-span-2' => auth()->user()?->can('printing.artwork.analyze'), 'lg:col-span-3' => ! auth()->user()?->can('printing.artwork.analyze')])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(['lg:col-span-2' => auth()->user()?->can('printing.artwork.analyze'), 'lg:col-span-3' => ! auth()->user()?->can('printing.artwork.analyze')]))]); ?>
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Recent analyses')); ?></h3>

            <?php if($analyses->isEmpty()): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No artwork analyses yet. Upload a file to begin.')); ?></p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3"><?php echo e(__('File')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Metadata')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Colour')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Coverage')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('CMYK')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('White')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Class')); ?></th>
                                <th class="py-2 pr-3"><?php echo e(__('Warn')); ?></th>
                                <th class="py-2"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__currentLoopData = $analyses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-2 pr-3">
                                        <div class="font-medium text-slate-900"><?php echo e($item->original_filename); ?></div>
                                        <div class="text-xs uppercase text-slate-500"><?php echo e($item->file_extension); ?></div>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <?php if($item->analysis_status): ?>
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->analysis_status->badgeClass()]); ?>">
                                                <?php echo e($item->analysis_status->label()); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <?php if($item->colour_analysis_status): ?>
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->colour_analysis_status->badgeClass()]); ?>">
                                                <?php echo e($item->colour_analysis_status->label()); ?>

                                            </span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 pr-3"><?php echo e($item->cmyk_coverage_percent !== null ? number_format((float) $item->cmyk_coverage_percent, 1).'%' : '—'); ?></td>
                                    <td class="py-2 pr-3 text-xs">
                                        <?php if($item->cyan_coverage_percent !== null): ?>
                                            C<?php echo e(number_format((float) $item->cyan_coverage_percent, 0)); ?>

                                            M<?php echo e(number_format((float) $item->magenta_coverage_percent, 0)); ?>

                                            Y<?php echo e(number_format((float) $item->yellow_coverage_percent, 0)); ?>

                                            K<?php echo e(number_format((float) $item->black_coverage_percent, 0)); ?>

                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 pr-3"><?php echo e($item->white_area_percent !== null ? number_format((float) $item->white_area_percent, 1).'%' : '—'); ?></td>
                                    <td class="py-2 pr-3">
                                        <?php if($item->coverage_class): ?>
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $item->coverage_class->badgeClass()]); ?>">
                                                <?php echo e($item->coverage_class->label()); ?>

                                            </span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 pr-3"><?php echo e(count($item->colour_analysis_warnings ?? [])); ?></td>
                                    <td class="py-2">
                                        <a href="<?php echo e(route('admin.printing-intelligence.artwork-analysis.show', $item)); ?>"
                                           class="text-xs font-medium text-slate-900 hover:underline"><?php echo e(__('View')); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\artwork-analysis\index.blade.php ENDPATH**/ ?>