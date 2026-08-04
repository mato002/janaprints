<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Notification Center'),'breadcrumbs' => [['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Notification Center')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="notification-center min-w-0" x-data="notificationCenterWorkspace(<?php echo \Illuminate\Support\Js::from($bootstrap)->toHtml() ?>)">
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Notification Center'),'description' => __('Internal ERP alerts for approvals, production, accounting, HR, and system events. No SMS, email, or WhatsApp in this phase.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Notification Center')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Internal ERP alerts for approvals, production, accounting, HR, and system events. No SMS, email, or WhatsApp in this phase.'))]); ?>
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

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Total'),'value' => $summary['total']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Unread'),'value' => $summary['unread']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Unread')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['unread'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Critical'),'value' => $summary['critical']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Critical')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['critical'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Archived'),'value' => $summary['archived']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Archived')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['archived'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <?php $__currentLoopData = [
                ['view' => null, 'label' => __('All')],
                ['view' => 'unread', 'label' => __('Unread')],
                ['view' => 'critical', 'label' => __('Critical')],
                ['view' => 'archived', 'label' => __('Archived')],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(route('admin.communications.notifications.index', array_merge(request()->except('view'), $tab['view'] ? ['view' => $tab['view']] : []))); ?>"
                    data-turbo-frame="erp-main"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors <?php echo e((request('view') ?: null) === $tab['view'] ? 'border-erp-accent bg-erp-accent/5 text-erp-primary' : 'border-erp-border text-slate-600 hover:border-erp-accent/40'); ?>"
                >
                    <?php echo e($tab['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="grid gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                    <form method="GET" action="<?php echo e(route('admin.communications.notifications.index')); ?>" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-index-toolbar-form" data-turbo-frame="erp-main">
                        <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <select name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>">
                                    <option value=""><?php echo e(__('All')); ?></option>
                                    <?php $__currentLoopData = \App\Enums\NotificationReadStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status->value); ?>" <?php if(request('status') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <select name="priority" class="erp-toolbar-select" aria-label="<?php echo e(__('Priority')); ?>">
                                    <option value=""><?php echo e(__('All')); ?></option>
                                    <?php $__currentLoopData = \App\Enums\NotificationPriority::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($priority->value); ?>" <?php if(request('priority') === $priority->value): echo 'selected'; endif; ?>><?php echo e($priority->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <select name="type" class="erp-toolbar-select" aria-label="<?php echo e(__('Type')); ?>">
                                    <option value=""><?php echo e(__('All')); ?></option>
                                    <?php $__currentLoopData = \App\Enums\NotificationType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type->value); ?>" <?php if(request('type') === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php if($users->isNotEmpty()): ?>
                                    <select name="user_id" class="erp-toolbar-select" aria-label="<?php echo e(__('User')); ?>">
                                        <option value=""><?php echo e(__('All users')); ?></option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>" <?php if((int) request('user_id') === $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php endif; ?>
                                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From')); ?>">
                                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To')); ?>">
                                <?php if(request('view')): ?>
                                    <input type="hidden" name="view" value="<?php echo e(request('view')); ?>">
                                <?php endif; ?>
                                <a href="<?php echo e(route('admin.communications.notifications.index')); ?>" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="erp-main"><?php echo e(__('Reset')); ?></a>
                            </div>
                        </div>
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

                <div class="erp-card overflow-hidden p-0">
                    <?php if($bootstrap['can']['manage']): ?>
                        <div class="flex flex-wrap items-center gap-2 border-b border-erp-border px-4 py-2">
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="bulkRead()"><?php echo e(__('Mark selected read')); ?></button>
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="bulkDismiss()"><?php echo e(__('Dismiss selected')); ?></button>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full">
                            <thead>
                                <tr>
                                    <?php if($bootstrap['can']['manage']): ?>
                                        <th class="w-8"><input type="checkbox" @change="toggleAll($event)"></th>
                                    <?php endif; ?>
                                    <th><?php echo e(__('Notification')); ?></th>
                                    <th><?php echo e(__('Type')); ?></th>
                                    <th><?php echo e(__('Priority')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $openUrl = $notification->resolved_action_url
                                            ?? route('admin.communications.notifications.index');
                                    ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <?php if($bootstrap['can']['manage'] && $notification->recipient_user_id === auth()->id()): ?>
                                            <td><input type="checkbox" value="<?php echo e($notification->id); ?>" x-model="selectedIds"></td>
                                        <?php elseif($bootstrap['can']['manage']): ?>
                                            <td></td>
                                        <?php endif; ?>
                                        <td>
                                            <a
                                                href="<?php echo e($openUrl); ?>"
                                                data-turbo-frame="erp-main"
                                                class="block no-underline"
                                                @click="openNotification(<?php echo e($notification->id); ?>, $event, <?php echo \Illuminate\Support\Js::from($openUrl)->toHtml() ?>)"
                                            >
                                                <p class="font-medium text-erp-primary hover:underline"><?php echo e($notification->title); ?></p>
                                                <p class="text-xs text-slate-600 line-clamp-1"><?php echo e($notification->body); ?></p>
                                            </a>
                                            <?php if($bootstrap['can']['admin'] && $notification->recipient): ?>
                                                <p class="text-[10px] text-slate-400"><?php echo e($notification->recipient->name); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-xs"><?php echo e($notification->type->label()); ?></td>
                                        <td>
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase <?php echo e($notification->priority->badgeClass()); ?>">
                                                <?php echo e($notification->priority->label()); ?>

                                            </span>
                                        </td>
                                        <td class="text-xs"><?php echo e($notification->readState?->status->label()); ?></td>
                                        <td class="text-xs tabular-nums text-slate-500"><?php echo e($notification->created_at?->format('d M Y H:i')); ?></td>
                                        <td class="text-right">
                                            <?php if($notification->recipient_user_id === auth()->id() && $bootstrap['can']['manage']): ?>
                                                <div class="flex justify-end gap-1">
                                                    <?php if($notification->readState?->status === \App\Enums\NotificationReadStatus::Unread): ?>
                                                        <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="markRead(<?php echo e($notification->id); ?>)"><?php echo e(__('Read')); ?></button>
                                                    <?php endif; ?>
                                                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="dismiss(<?php echo e($notification->id); ?>)"><?php echo e(__('Dismiss')); ?></button>
                                                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="archive(<?php echo e($notification->id); ?>)"><?php echo e(__('Archive')); ?></button>
                                                    <a
                                                        href="<?php echo e($openUrl); ?>"
                                                        data-turbo-frame="erp-main"
                                                        class="erp-btn erp-btn--ghost erp-btn--xs"
                                                        @click="openNotification(<?php echo e($notification->id); ?>, $event, <?php echo \Illuminate\Support\Js::from($openUrl)->toHtml() ?>)"
                                                    ><?php echo e(__('Open')); ?></a>
                                                </div>
                                            <?php else: ?>
                                                <a
                                                    href="<?php echo e($openUrl); ?>"
                                                    data-turbo-frame="erp-main"
                                                    class="erp-btn erp-btn--ghost erp-btn--xs"
                                                    @click="openNotification(<?php echo e($notification->id); ?>, $event, <?php echo \Illuminate\Support\Js::from($openUrl)->toHtml() ?>)"
                                                ><?php echo e(__('Open')); ?></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-500"><?php echo e(__('No notifications match your filters.')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($notifications->hasPages()): ?>
                        <div class="border-t border-erp-border px-4 py-3">
                            <?php echo e($notifications->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-4 xl:col-span-4">
                <?php if($prefs): ?>
                    <div class="erp-card">
                        <h2 class="erp-card-title"><?php echo e(__('Alert preferences')); ?></h2>
                        <p class="text-xs text-slate-500 mb-3"><?php echo e(__('Control which alert categories you receive.')); ?></p>
                        <form @submit.prevent="savePreferences()" class="space-y-2">
                            <?php $__currentLoopData = [
                                'commercial_alerts' => __('Commercial alerts'),
                                'production_alerts' => __('Production alerts'),
                                'accounting_alerts' => __('Accounting alerts'),
                                'hr_alerts' => __('HR alerts'),
                                'system_alerts' => __('System alerts'),
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center justify-between gap-2 rounded-lg border border-erp-border px-3 py-2">
                                    <span class="text-sm text-erp-primary"><?php echo e($label); ?></span>
                                    <input type="checkbox" class="rounded border-erp-border text-erp-accent" x-model="preferences['<?php echo e($key); ?>']">
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm mt-2 w-full" :disabled="prefsSaving">
                                <?php echo e(__('Save preferences')); ?>

                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="erp-card">
                    <h2 class="erp-card-title"><?php echo e(__('Recent activity')); ?></h2>
                    <ul class="mt-2 space-y-2 text-xs text-slate-600">
                        <?php $__currentLoopData = $notifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="border-b border-erp-border/50 pb-2 last:border-0">
                                <span class="font-medium text-erp-primary"><?php echo e($notification->title); ?></span>
                                <span class="text-slate-400"> · <?php echo e($notification->created_at?->diffForHumans()); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($notifications->isEmpty()): ?>
                            <li class="text-slate-500"><?php echo e(__('No recent notifications.')); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if($bootstrap['can']['create']): ?>
                    <div class="erp-card" x-show="can.create" x-cloak>
                        <h2 class="erp-card-title"><?php echo e(__('Send test alert')); ?></h2>
                        <p class="text-xs text-slate-500 mb-3"><?php echo e(__('Internal delivery only — for verification.')); ?></p>
                        <form @submit.prevent="sendTest()" class="space-y-2">
                            <?php if($bootstrap['can']['admin'] && $users->isNotEmpty()): ?>
                                <div>
                                    <label class="erp-label text-xs"><?php echo e(__('Recipient')); ?></label>
                                    <select class="erp-input w-full" x-model="testForm.recipient_user_id" required>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div>
                                <label class="erp-label text-xs"><?php echo e(__('Type')); ?></label>
                                <select class="erp-input w-full" x-model="testForm.type" required>
                                    <template x-for="t in types" :key="t.value">
                                        <option :value="t.value" x-text="t.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label text-xs"><?php echo e(__('Title')); ?></label>
                                <input type="text" class="erp-input w-full" x-model="testForm.title" required>
                            </div>
                            <div>
                                <label class="erp-label text-xs"><?php echo e(__('Body')); ?></label>
                                <textarea class="erp-input w-full" rows="3" x-model="testForm.body" required></textarea>
                            </div>
                            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm w-full"><?php echo e(__('Send')); ?></button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/notifications/index.blade.php ENDPATH**/ ?>