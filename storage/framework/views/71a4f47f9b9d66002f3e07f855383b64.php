<?php
    use App\Providers\AppServiceProvider as Nav;
    $active = Nav::navItemIsActive($child);
    $comingSoon = ! empty($child['coming_soon']);
?>

<?php if($comingSoon): ?>
    <span
        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed <?php echo e(! empty($collapsed) ? 'lg:justify-center lg:px-2' : 'pl-9'); ?>"
        title="<?php echo e(__('Coming soon')); ?>"
    >
        <?php if(! empty($child['icon'])): ?>
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $child['icon'],'class' => 'w-4 h-4 shrink-0 opacity-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($child['icon']),'class' => 'w-4 h-4 shrink-0 opacity-50']); ?>
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
        <?php endif; ?>
        <span <?php if(! empty($collapsed)): ?> x-cloak <?php endif; ?> class="<?php echo e(! empty($collapsed) ? 'sr-only lg:not-sr-only lg:hidden' : ''); ?>"><?php echo e($child['label']); ?></span>
        <?php if(empty($collapsed)): ?>
            <span class="ml-auto text-[10px] uppercase tracking-wide text-slate-600"><?php echo e(__('Soon')); ?></span>
        <?php endif; ?>
    </span>
<?php else: ?>
    <a
        href="<?php echo e(route($child['route'])); ?>"
        data-turbo-frame="erp-main"
        data-turbo-action="advance"
        data-turbo-preload="hover"
        data-nav-route="<?php echo e($child['route']); ?>"
        <?php if(! empty($child['active_routes'])): ?>
            data-nav-active-routes="<?php echo e(implode(',', $child['active_routes'])); ?>"
        <?php endif; ?>
        data-nav-depth="child"
        @click="$dispatch('close-nav')"
        class="group/link erp-nav-link <?php echo e($active ? 'erp-nav-link--active border-l-3 border-erp-accent bg-erp-primary text-white' : ''); ?> <?php echo e(! empty($collapsed) ? 'lg:justify-center lg:px-2 px-3' : 'px-3'); ?> <?php echo e(empty($collapsed) && ! empty($indent) ? 'pl-6' : (empty($collapsed) ? 'pl-9' : '')); ?>"
        title="<?php echo e($child['label']); ?>"
    >
        <?php if(! empty($child['icon'])): ?>
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $child['icon'],'class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($child['icon']),'class' => 'w-4 h-4 shrink-0']); ?>
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
        <?php endif; ?>
        <span class="<?php echo e(! empty($collapsed) ? 'sr-only' : 'truncate'); ?>"><?php echo e($child['label']); ?></span>
        <?php if(empty($collapsed) && ! empty($child['route'])): ?>
            <button
                type="button"
                class="ml-auto hidden rounded p-0.5 text-slate-500 hover:text-amber-300 group-hover/link:inline-flex"
                :class="isFavorite('<?php echo e($child['route']); ?>') ? '!inline-flex text-amber-400' : ''"
                @click.prevent.stop="toggleFavorite('<?php echo e($child['route']); ?>')"
                :title="isFavorite('<?php echo e($child['route']); ?>') ? '<?php echo e(__('Unpin')); ?>' : '<?php echo e(__('Pin to favorites')); ?>'"
                aria-label="<?php echo e(__('Pin menu item')); ?>"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </button>
        <?php endif; ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\layouts\admin\partials\sidebar-link.blade.php ENDPATH**/ ?>