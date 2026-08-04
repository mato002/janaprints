<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Roles'),'breadcrumbs' => [
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Roles')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        x-data="roleGovernanceDashboard()"
        @scroll.window="clearPreview()"
        @resize.window="clearPreview()"
    >
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-erp-primary"><?php echo e(__('Security Groups')); ?></h1>
                <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Enterprise access governance across departments and job functions.')); ?></p>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', Spatie\Permission\Models\Role::class)): ?>
                <a href="<?php echo e(route('admin.roles.create')); ?>" data-turbo-frame="erp-main" data-turbo-action="advance" class="erp-btn-primary !px-3 !py-1.5 text-sm"><?php echo e(__('Create role')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', Spatie\Permission\Models\Role::class)): ?>
                <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => 'admin.roles.export','exportQuery' => request()->query(),'formatInPath' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => 'admin.roles.export','export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query()),'format-in-path' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="role-governance-panel mb-3 rounded-lg border border-erp-border bg-erp-page/60 px-4 py-2.5">
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                <span><span class="text-slate-400"><?php echo e(__('Roles')); ?>:</span> <strong class="text-erp-primary"><?php echo e(number_format($panel['total_roles'])); ?></strong></span>
                <span><span class="text-slate-400"><?php echo e(__('Active')); ?>:</span> <strong class="text-emerald-700"><?php echo e(number_format($panel['active'])); ?></strong></span>
                <span><span class="text-slate-400"><?php echo e(__('Draft')); ?>:</span> <strong class="text-sky-700"><?php echo e(number_format($panel['draft'])); ?></strong></span>
                <span><span class="text-slate-400"><?php echo e(__('Broken')); ?>:</span> <strong class="text-red-700"><?php echo e(number_format($panel['broken'])); ?></strong></span>
                <span><span class="text-slate-400"><?php echo e(__('Unused')); ?>:</span> <strong class="text-slate-700"><?php echo e(number_format($panel['unused'])); ?></strong></span>
                <?php if($panel['deactivated'] > 0): ?>
                    <span><span class="text-slate-400"><?php echo e(__('Deactivated')); ?>:</span> <strong class="text-slate-500"><?php echo e(number_format($panel['deactivated'])); ?></strong></span>
                <?php endif; ?>
                <span><span class="text-slate-400"><?php echo e(__('Users assigned')); ?>:</span> <strong class="text-erp-primary"><?php echo e(number_format($panel['assigned_users'])); ?></strong></span>
            </div>
        </div>

        <div class="mb-3 flex flex-wrap gap-x-5 gap-y-1 rounded-lg border border-erp-border bg-white px-4 py-2 text-[11px] text-slate-600">
            <?php if($insights['most_used']): ?>
                <span><span class="font-medium text-slate-500"><?php echo e(__('Most used')); ?>:</span> <?php echo e($insights['most_used']['name']); ?> (<?php echo e($insights['most_used']['users_count']); ?>)</span>
            <?php endif; ?>
            <?php if($insights['least_used']): ?>
                <span><span class="font-medium text-slate-500"><?php echo e(__('Least used')); ?>:</span> <?php echo e($insights['least_used']['name']); ?> (<?php echo e($insights['least_used']['users_count']); ?>)</span>
            <?php endif; ?>
            <span><span class="font-medium text-slate-500"><?php echo e(__('Without users')); ?>:</span> <?php echo e(number_format($insights['roles_without_users'])); ?></span>
            <span><span class="font-medium text-slate-500"><?php echo e(__('Without permissions')); ?>:</span> <?php echo e(number_format($insights['roles_without_permissions'])); ?></span>
            <span><span class="font-medium text-slate-500"><?php echo e(__('Broken roles')); ?>:</span> <?php echo e(number_format($insights['broken_roles'])); ?></span>
            <span><span class="font-medium text-slate-500"><?php echo e(__('Draft roles')); ?>:</span> <?php echo e(number_format($insights['draft_roles'])); ?></span>
        </div>

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
            <div class="border-b border-erp-border px-4 py-2.5">
                <div class="relative max-w-md">
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
                        x-model="query"
                        class="erp-input w-full py-1.5 pl-8 text-sm"
                        placeholder="<?php echo e(__('Search roles, categories, modules, users…')); ?>"
                        aria-label="<?php echo e(__('Search roles')); ?>"
                    >
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid text-sm">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Role')); ?></th>
                            <th><?php echo e(__('Category')); ?></th>
                            <th><?php echo e(__('Users assigned')); ?></th>
                            <th><?php echo e(__('Permissions')); ?></th>
                            <th><?php echo e(__('Modules')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th class="text-right"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-erp-border bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr
                                x-show="matches(<?php echo \Illuminate\Support\Js::from($profile['search_text'])->toHtml() ?>)"
                                class="cursor-pointer transition-colors hover:bg-slate-50/80 <?php echo e($profile['is_deactivated'] ? 'bg-slate-50/80 opacity-75' : ''); ?>"
                                @click="openRole(<?php echo \Illuminate\Support\Js::from($profile['show_url'])->toHtml() ?>)"
                                @mouseenter="setPreview(<?php echo \Illuminate\Support\Js::from($profile)->toHtml() ?>, $event)"
                                @mouseleave="clearPreview()"
                            >
                                <td class="py-2.5">
                                    <span data-role-preview-anchor class="font-medium text-erp-primary">
                                        <?php echo e($profile['name']); ?>

                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <span class="role-category-badge role-category-badge--<?php echo e($profile['category']['tone']); ?>">
                                        <?php echo e($profile['category']['label']); ?>

                                    </span>
                                </td>
                                <td class="py-2.5 tabular-nums" @click.stop>
                                    <?php if($profile['users_count'] > 0): ?>
                                        <button
                                            type="button"
                                            @click.stop="openDrawer(<?php echo \Illuminate\Support\Js::from($profile)->toHtml() ?>)"
                                            class="font-medium text-erp-accent hover:underline"
                                        >
                                            <?php echo e(number_format($profile['users_count'])); ?>

                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-400">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 tabular-nums"><?php echo e(number_format($profile['permissions_count'])); ?></td>
                                <td class="max-w-[12rem] py-2.5">
                                    <?php if($profile['modules_enabled'] > 0): ?>
                                        <span class="block truncate text-slate-700" title="<?php echo e($profile['modules_display']); ?>">
                                            <?php echo e($profile['modules_display']); ?>

                                        </span>
                                        <span class="text-[10px] text-slate-400"><?php echo e(__(':count enabled', ['count' => $profile['modules_enabled']])); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5">
                                    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $profile['health']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile['health']['tone'])]); ?>
                                        <?php echo e($profile['health']['label']); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                </td>
                                <td class="py-2.5 text-right" @click.stop>
                                    <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                                         <?php $__env->slot('trigger', null, []); ?> 
                                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-erp-page hover:text-erp-primary" aria-label="<?php echo e(__('Role actions')); ?>">
                                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'ellipsis-vertical','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ellipsis-vertical','class' => 'h-4 w-4']); ?>
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
                                         <?php $__env->endSlot(); ?>
                                         <?php $__env->slot('content', null, []); ?> 
                                            <a href="<?php echo e($profile['show_url']); ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-erp-page" data-turbo-action="advance"><?php echo e(__('Open')); ?></a>
                                            <?php if($profile['can_clone']): ?>
                                                <form method="POST" action="<?php echo e(route('admin.roles.duplicate', $profile['id'])); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"><?php echo e(__('Clone')); ?></button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($profile['edit_url']): ?>
                                                <a href="<?php echo e($profile['edit_url']); ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-erp-page" data-turbo-action="advance"><?php echo e(__('Rename')); ?></a>
                                            <?php endif; ?>
                                            <?php if($profile['can_deactivate']): ?>
                                                <?php if($profile['is_deactivated']): ?>
                                                    <form method="POST" action="<?php echo e(route('admin.roles.reactivate', $profile['id'])); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PATCH'); ?>
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"><?php echo e(__('Reactivate')); ?></button>
                                                    </form>
                                                <?php elseif($profile['deactivate_blocked']): ?>
                                                    <span class="block px-4 py-2 text-sm text-slate-400" title="<?php echo e(__('Remove users before deactivating.')); ?>"><?php echo e(__('Deactivate')); ?></span>
                                                <?php else: ?>
                                                    <form method="POST" action="<?php echo e(route('admin.roles.deactivate', $profile['id'])); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Deactivate this role? Permissions are preserved for audit history.'))->toHtml() ?>)">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PATCH'); ?>
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-amber-700 hover:bg-amber-50"><?php echo e(__('Deactivate')); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                         <?php $__env->endSlot(); ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">
                                    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'key','title' => __('No roles found')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'key','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No roles found'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
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

        <div class="mt-4"><?php echo e($roles->links()); ?></div>

        <div
            x-ref="rolePreview"
            x-show="previewRole"
            x-cloak
            x-transition
            :style="previewStyle"
            class="role-governance-preview pointer-events-none fixed z-[55] w-64 rounded-lg border border-erp-border bg-white p-3 text-xs shadow-lg"
        >
            <p class="font-semibold text-erp-primary" x-text="previewRole?.name"></p>
            <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e(__('Access coverage')); ?></p>
            <ul class="mt-1.5 space-y-0.5">
                <template x-for="module in previewRole?.module_coverage ?? []" :key="module.key">
                    <li class="flex items-center justify-between gap-2 text-slate-600">
                        <span x-text="module.label"></span>
                        <span class="font-semibold" :class="module.enabled ? 'text-emerald-600' : 'text-slate-300'" x-text="module.enabled ? '✓' : '✗'"></span>
                    </li>
                </template>
            </ul>
        </div>

        <div
            x-show="drawerOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/30"
            @click="closeDrawer()"
            style="display: none;"
        ></div>

        <div
            x-show="drawerOpen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="role-governance-drawer"
            @keydown.escape.window="closeDrawer()"
            style="display: none;"
        >
            <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                <div>
                    <p class="text-xs text-slate-500"><?php echo e(__('Assigned users')); ?></p>
                    <h2 class="text-base font-semibold text-erp-primary" x-text="drawerRole?.name"></h2>
                </div>
                <button type="button" @click="closeDrawer()" class="rounded-lg p-1.5 text-slate-500 hover:bg-erp-page" aria-label="<?php echo e(__('Close')); ?>">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'x-mark','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'h-4 w-4']); ?>
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
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="drawerRole && drawerRole.users.length === 0">
                    <p class="text-sm text-slate-500"><?php echo e(__('No users assigned to this role yet.')); ?></p>
                </template>
                <ul class="space-y-2" x-show="drawerRole && drawerRole.users.length > 0">
                    <template x-for="user in drawerRole?.users ?? []" :key="user.id">
                        <li class="rounded-lg border border-erp-border px-3 py-2">
                            <template x-if="user.edit_url">
                                <a :href="user.edit_url" class="block font-medium text-erp-primary hover:text-erp-accent" data-turbo-action="advance" x-text="user.name"></a>
                            </template>
                            <template x-if="! user.edit_url">
                                <span class="block font-medium text-erp-primary" x-text="user.name"></span>
                            </template>
                            <p class="text-xs text-slate-500" x-text="user.email"></p>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="border-t border-erp-border px-4 py-3">
                <a
                    :href="drawerRole?.show_url"
                    class="erp-btn-secondary w-full justify-center !py-1.5 text-sm"
                    data-turbo-action="advance"
                >
                    <?php echo e(__('Open role permissions')); ?>

                </a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\roles\index.blade.php ENDPATH**/ ?>