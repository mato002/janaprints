<?php
    $bell = $notificationBellBootstrap ?? ['enabled' => false];
?>

<?php if($bell['enabled'] ?? false): ?>
    <div
        class="relative"
        x-data="erpNotificationBell(<?php echo \Illuminate\Support\Js::from($bell)->toHtml() ?>)"
        @keydown.escape.window="close()"
        @scroll.window="open && placePanel()"
        @resize.window="open && placePanel()"
    >
        <button
            x-ref="trigger"
            type="button"
            class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-erp-page hover:text-slate-700"
            :class="open ? 'bg-erp-page text-erp-primary' : ''"
            title="<?php echo e(__('Notifications')); ?>"
            aria-label="<?php echo e(__('Notifications')); ?>"
            aria-expanded="false"
            :aria-expanded="open"
            @click="toggle()"
        >
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5']); ?>
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
            <span
                x-show="unreadCount > 0"
                x-cloak
                class="absolute -right-0.5 -top-0.5 flex min-h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                x-text="unreadCount > 99 ? '99+' : unreadCount"
            ></span>
        </button>

        <div
            x-ref="panel"
            x-show="open"
            x-cloak
            x-transition
            :style="panelStyle"
            class="erp-notification-panel w-[min(100vw-2rem,22rem)] overflow-hidden rounded-xl border border-erp-border bg-white shadow-card-hover sm:w-96"
        >
            <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Notifications')); ?></h2>
                <button
                    type="button"
                    class="text-xs font-medium text-erp-accent hover:underline"
                    x-show="unreadCount > 0"
                    @click="markAllRead()"
                >
                    <?php echo e(__('Mark all read')); ?>

                </button>
            </div>

            <div class="max-h-[min(60vh,20rem)] overflow-y-auto">
                <template x-if="loading">
                    <p class="px-4 py-6 text-center text-sm text-slate-500"><?php echo e(__('Loading…')); ?></p>
                </template>
                <template x-if="!loading && items.length === 0">
                    <p class="px-4 py-6 text-center text-sm text-slate-500"><?php echo e(__('No notifications')); ?></p>
                </template>
                <template x-for="item in items" :key="item.id">
                    <div
                        class="border-b border-erp-border/60 transition-colors hover:bg-slate-50/80"
                        :class="item.is_unread ? 'bg-erp-accent/[0.03]' : ''"
                    >
                        <a
                            :href="notificationHref(item)"
                            data-turbo-frame="erp-main"
                            class="block px-4 py-3 text-left no-underline"
                            @click="openNotification(item, $event)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-erp-primary line-clamp-1" x-text="item.title"></p>
                                    <p class="mt-0.5 text-xs text-slate-600 line-clamp-2" x-text="item.body"></p>
                                </div>
                                <span
                                    class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase"
                                    :class="item.priority_badge"
                                    x-text="item.priority_label"
                                ></span>
                            </div>
                        </a>
                        <div class="flex items-center justify-between gap-2 px-4 pb-3">
                            <span class="text-[10px] text-slate-500" x-text="formatDate(item.created_at)"></span>
                            <button
                                type="button"
                                class="text-[10px] font-medium text-erp-accent hover:underline"
                                x-show="item.is_unread"
                                @click.stop="markRead(item)"
                            >
                                <?php echo e(__('Mark read')); ?>

                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t border-erp-border bg-slate-50/50 px-4 py-2.5">
                <a
                    :href="routes.center"
                    data-turbo-frame="erp-main"
                    class="block text-center text-xs font-semibold text-erp-accent hover:underline"
                    @click="close()"
                >
                    <?php echo e(__('View all notifications')); ?>

                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <button type="button" class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-erp-page hover:text-slate-700" title="<?php echo e(__('Notifications')); ?>" aria-label="<?php echo e(__('Notifications')); ?>" disabled>
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5']); ?>
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
    </button>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/layouts/admin/partials/notification-bell.blade.php ENDPATH**/ ?>