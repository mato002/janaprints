<?php
    $summary = $controlCenter['summary'];
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeAction = WorkspaceEmbed::url(route('admin.settings.show', 'hub'));
    $embedded = WorkspaceEmbed::inWorkspaceContext();
?>

<div
    x-data="settingsControlCenter(<?php echo \Illuminate\Support\Js::from($controlCenter['cards'])->toHtml() ?>)"
    x-cloak
    class="settings-workspace w-full min-w-0"
>
    
    <div class="settings-workspace-toolbar mb-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 shadow-sm">
        <h1 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Settings Control Center')); ?></h1>

        <div class="flex flex-wrap items-center gap-2">
            <?php if($companies->count() > 1 || $branches->isNotEmpty()): ?>
                <form method="GET" action="<?php echo e($scopeAction); ?>" class="flex flex-wrap items-center gap-2">
                    <?php if($embedded): ?>
                        <input type="hidden" name="embedded" value="1">
                    <?php endif; ?>
                    <?php if($companies->count() > 1): ?>
                        <label class="flex items-center gap-1.5 text-[11px] text-erp-primary">
                            <span class="text-slate-500"><?php echo e(__('Company')); ?></span>
                            <select name="company_id" class="erp-select py-1 pl-2 pr-7 text-xs" onchange="this.form.submit()">
                                <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($company->id); ?>" <?php if($companyId === $company->id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    <?php else: ?>
                        <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
                    <?php endif; ?>

                    <?php if($branches->isNotEmpty()): ?>
                        <label class="flex items-center gap-1.5 text-[11px] text-erp-primary">
                            <span class="text-slate-500"><?php echo e(__('Branch')); ?></span>
                            <select name="branch_id" class="erp-select py-1 pl-2 pr-7 text-xs" onchange="this.form.submit()">
                                <option value=""><?php echo e(__('All branches')); ?></option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php if($branchId === $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <div class="inline-flex rounded-md border border-erp-border bg-erp-page/50 p-0.5 text-[11px]">
                <button
                    type="button"
                    class="rounded px-2 py-0.5 font-medium transition-colors"
                    :class="viewMode === 'grid' ? 'bg-erp-primary text-white shadow-sm' : 'text-slate-500 hover:text-erp-accent'"
                    @click="setViewMode('grid')"
                >
                    <?php echo e(__('Grid')); ?>

                </button>
                <button
                    type="button"
                    class="rounded px-2 py-0.5 font-medium transition-colors"
                    :class="viewMode === 'list' ? 'bg-erp-primary text-white shadow-sm' : 'text-slate-500 hover:text-erp-accent'"
                    @click="setViewMode('list')"
                >
                    <?php echo e(__('List')); ?>

                </button>
            </div>
        </div>
    </div>

    
    <div class="erp-stats-strip mb-2 rounded-lg border border-erp-border bg-white px-3 py-2 shadow-sm">
        <span><span class="text-slate-400"><?php echo e(__('Areas')); ?>:</span> <strong class="text-erp-primary"><?php echo e(number_format($summary['total_areas'])); ?></strong></span>
        <span><span class="text-slate-400"><?php echo e(__('Configured')); ?>:</span> <strong class="text-emerald-700"><?php echo e(number_format($summary['configured'])); ?></strong></span>
        <?php if($summary['needs_attention'] > 0): ?>
            <span><span class="text-slate-400"><?php echo e(__('Incomplete')); ?>:</span> <strong class="text-amber-700"><?php echo e(number_format($summary['needs_attention'])); ?></strong></span>
        <?php endif; ?>
        <span><span class="text-slate-400"><?php echo e(__('Pending')); ?>:</span> <strong class="text-slate-600"><?php echo e(number_format($summary['pending_setup'])); ?></strong></span>
    </div>

    
    <div class="relative mb-2">
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
            class="erp-input w-full py-1.5 pl-8 text-xs"
            placeholder="<?php echo e(__('Search settings…')); ?>"
            aria-label="<?php echo e(__('Search settings')); ?>"
        >
    </div>

    
    <div class="sticky top-0 z-20 -mx-1 mb-3 border-b border-erp-border bg-white/95 px-1 pb-2 pt-1 backdrop-blur supports-[backdrop-filter]:bg-white/90">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <?php $__currentLoopData = $controlCenter['filters']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    type="button"
                    class="erp-filter-pill"
                    :class="activeFilter === <?php echo \Illuminate\Support\Js::from($filter['slug'])->toHtml() ?> ? 'erp-filter-pill--active' : ''"
                    @click="setFilter(<?php echo \Illuminate\Support\Js::from($filter['slug'])->toHtml() ?>)"
                >
                    <?php echo e($filter['label']); ?>

                    <span class="ml-1 opacity-70"><?php echo e($filter['count']); ?></span>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div
        x-show="viewMode === 'grid'"
        class="grid w-full grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
    >
        <?php $__currentLoopData = $controlCenter['cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="min-w-0" x-show="cardVisible(<?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>)">
                <?php echo $__env->make('admin.settings.partials.settings-tile', [
                    'title' => $card['title'],
                    'description' => $card['description'],
                    'icon' => $card['icon'],
                    'href' => $card['href'],
                    'comingSoon' => $card['comingSoon'],
                    'statusLabel' => $card['statusLabel'],
                    'statusVariant' => $card['statusVariant'],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div x-show="viewMode === 'list'" x-cloak class="space-y-1">
        <?php $__currentLoopData = $controlCenter['cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div x-show="cardVisible(<?php echo \Illuminate\Support\Js::from($card['id'])->toHtml() ?>)">
                <?php echo $__env->make('admin.settings.partials.settings-list-row', [
                    'title' => $card['title'],
                    'description' => $card['description'],
                    'icon' => $card['icon'],
                    'href' => $card['href'],
                    'comingSoon' => $card['comingSoon'],
                    'statusLabel' => $card['statusLabel'],
                    'statusVariant' => $card['statusVariant'],
                    'domainLabel' => $card['domain_label'],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <p
        x-show="visibleCount === 0"
        x-cloak
        class="rounded-lg border border-dashed border-erp-border px-4 py-6 text-center text-sm text-slate-500"
    >
        <?php echo e(__('No settings match your search or filter.')); ?>

    </p>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\hub-control-center.blade.php ENDPATH**/ ?>