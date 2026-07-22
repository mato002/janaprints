<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $workspace['title'],'compactWorkspace' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        x-data="workspaceHub(<?php echo \Illuminate\Support\Js::from($cards)->toHtml() ?>)"
        x-cloak
        class="workspace-hub w-full min-w-0 space-y-3"
    >
        <?php if (isset($component)) { $__componentOriginal35d357500b9bf1947b480677203677da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal35d357500b9bf1947b480677203677da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.compact-workspace-header','data' => ['title' => $workspace['title'],'description' => $workspace['description']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.compact-workspace-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workspace['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workspace['description'])]); ?>
             <?php $__env->slot('search', null, []); ?> 
                <div class="relative w-full sm:w-56">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']); ?>
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
                    <input
                        type="search"
                        x-model.debounce.150ms="query"
                        @input="syncVisibleCards()"
                        class="erp-input w-full py-1.5 pl-8 text-sm"
                        placeholder="<?php echo e(__('Search in :workspace…', ['workspace' => $workspace['title']])); ?>"
                        aria-label="<?php echo e(__('Search workspace')); ?>"
                        autocomplete="off"
                    >
                </div>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal35d357500b9bf1947b480677203677da)): ?>
<?php $attributes = $__attributesOriginal35d357500b9bf1947b480677203677da; ?>
<?php unset($__attributesOriginal35d357500b9bf1947b480677203677da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal35d357500b9bf1947b480677203677da)): ?>
<?php $component = $__componentOriginal35d357500b9bf1947b480677203677da; ?>
<?php unset($__componentOriginal35d357500b9bf1947b480677203677da); ?>
<?php endif; ?>

        <p
            x-show="normalizedQuery"
            x-cloak
            class="text-[11px] text-slate-500"
            x-text="visibleCount === 1 ? '<?php echo e(__('1 feature matches')); ?>' : `<?php echo e(__(':count features match')); ?>`.replace(':count', visibleCount)`"
        ></p>

        <?php $__currentLoopData = $workspace['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="mb-4" x-show="groupVisible(<?php echo \Illuminate\Support\Js::from($group['label'])->toHtml() ?>)" x-cloak>
                <h2 class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($group['label']); ?></h2>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div x-show="cardVisible(<?php echo \Illuminate\Support\Js::from($item['id'])->toHtml() ?>)" x-cloak>
                            <?php echo $__env->make('admin.settings.partials.settings-tile', [
                                'title' => $item['label'],
                                'description' => $item['description'],
                                'icon' => $item['icon'],
                                'href' => $item['href'],
                                'comingSoon' => $item['comingSoon'],
                                'statusLabel' => $item['statusLabel'],
                                'statusVariant' => $item['statusVariant'],
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <p
            x-show="visibleCount === 0"
            x-cloak
            class="rounded-lg border border-dashed border-erp-border px-4 py-6 text-center text-sm text-slate-500"
        >
            <?php echo e(__('No features match your search.')); ?>

        </p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\workspaces\show.blade.php ENDPATH**/ ?>