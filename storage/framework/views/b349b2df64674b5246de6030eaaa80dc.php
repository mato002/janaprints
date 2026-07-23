<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $document->title,'breadcrumbs' => [['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Documents'), 'url' => route('admin.hr.documents.dashboard')], ['label' => $document->title]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $document->title,'description' => $document->employee->full_name.' · '.$document->category->label()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($document->title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($document->employee->full_name.' · '.$document->category->label())]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.hr.documents.download', $document)); ?>" class="erp-btn-primary"><?php echo e(__('Download current')); ?></a>
            <a href="<?php echo e(route('admin.hr.documents.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Back')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $document)): ?>
                <form method="POST" action="<?php echo e(route('admin.hr.documents.destroy', $document)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this document and all versions?'))->toHtml() ?>)">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="erp-btn-secondary text-rose-700"><?php echo e(__('Delete')); ?></button>
                </form>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
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

<div class="grid gap-4 lg:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1','title' => __('Details')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Details'))]); ?>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Employee')); ?></dt>
                    <dd class="font-medium"><?php echo e($document->employee->full_name); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Category')); ?></dt>
                    <dd><?php echo e($document->category->label()); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Current Version')); ?></dt>
                    <dd>v<?php echo e($document->current_version); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Expiry')); ?></dt>
                    <dd>
                        <?php if($document->expires_at): ?>
                            <?php echo e($document->expires_at->format('Y-m-d')); ?>

                            <?php if($document->isExpired()): ?>
                                <span class="ml-1 rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700"><?php echo e(__('Expired')); ?></span>
                            <?php elseif($document->isExpiringSoon()): ?>
                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700"><?php echo e(__('Renewal due')); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if($document->description): ?>
                    <div>
                        <dt class="text-slate-500"><?php echo e(__('Description')); ?></dt>
                        <dd><?php echo e($document->description); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
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

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-2','title' => __('Version History')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Version History'))]); ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3"><?php echo e(__('Version')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('File')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Uploaded By')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Date')); ?></th>
                            <th class="py-2"><?php echo e(__('Notes')); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $document->versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">
                                    v<?php echo e($version->version_number); ?>

                                    <?php if($version->version_number === $document->current_version): ?>
                                        <span class="ml-1 text-xs text-emerald-600">(<?php echo e(__('current')); ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 pr-3"><?php echo e($version->original_name); ?></td>
                                <td class="py-2 pr-3"><?php echo e($version->uploadedBy?->name ?? '—'); ?></td>
                                <td class="py-2 pr-3"><?php echo e($version->created_at?->format('Y-m-d H:i')); ?></td>
                                <td class="py-2 pr-3"><?php echo e($version->notes ?? '—'); ?></td>
                                <td class="py-2 text-right">
                                    <a href="<?php echo e(route('admin.hr.documents.version.download', ['employeeDocument' => $document, 'employeeDocumentVersion' => $version])); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Download')); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('upload', $document)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4','title' => __('Upload New Version')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Upload New Version'))]); ?>
            <form method="POST" action="<?php echo e(route('admin.hr.documents.upload', $document)); ?>" enctype="multipart/form-data" class="max-w-2xl">
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="erp-label" for="file"><?php echo e(__('File')); ?></label>
                        <input id="file" type="file" name="file" class="erp-input w-full" required>
                        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label" for="notes"><?php echo e(__('Version Notes')); ?></label>
                        <input id="notes" type="text" name="notes" class="erp-input w-full" placeholder="<?php echo e(__('What changed in this version?')); ?>">
                    </div>
                </div>
                <button type="submit" class="erp-btn-primary mt-4"><?php echo e(__('Upload version')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\documents\show.blade.php ENDPATH**/ ?>