<header class="crm-360__header">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'sm','href' => route('admin.crm.leads.index'),'class' => '!px-2.5','dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.leads.index')),'class' => '!px-2.5','data-turbo-frame' => 'erp-main']); ?>← <?php echo e(__('Leads')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <h1 class="crm-360__title"><?php echo e($lead->lead_name); ?></h1>
            <p class="crm-360__subtitle">
                <?php if($lead->company_name): ?>
                    <span><?php echo e($lead->company_name); ?></span>
                <?php endif; ?>
                <?php if($lead->branch): ?>
                    <span class="text-slate-300" aria-hidden="true"> • </span>
                    <span><?php echo e($lead->branch->name); ?></span>
                <?php endif; ?>
            </p>
            <p class="crm-360__since">
                <?php echo e(__('Lead since')); ?> <?php echo e($lead->created_at?->format('M Y') ?? '—'); ?>

            </p>
            <span class="crm-360__status crm-360__status--<?php echo e($lead->status->value); ?>">
                <?php echo e(strtoupper(str_replace('_', ' ', $lead->status->value))); ?>

            </span>
        </div>

        <div class="crm-360__action-bar" x-data="{ moreOpen: false }">
            <div class="flex flex-wrap items-center gap-2">
                <?php echo $__env->make('admin.crm.leads.360.partials.quotation-actions', [
                    'variant' => 'primary',
                    'quickVariant' => 'outline',
                    'size' => 'md',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <?php if($lead->customer_id && $lead->customer): ?>
                <a href="<?php echo e(route('admin.crm.customers.show', $lead->customer)); ?>" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">
                    <?php echo e(__('Open customer')); ?>

                </a>
            <?php elseif(auth()->user()?->can('convert', $lead)): ?>
                <form method="POST" action="<?php echo e(route('admin.crm.leads.convert', $lead)); ?>" class="inline"><?php echo csrf_field(); ?>
                    <button type="submit" class="crm-360__btn crm-360__btn--outline"><?php echo e(__('Convert lead')); ?></button>
                </form>
            <?php endif; ?>

            <div class="relative">
                <button
                    type="button"
                    class="crm-360__btn crm-360__btn--ghost"
                    @click="moreOpen = !moreOpen"
                    :aria-expanded="moreOpen"
                    aria-haspopup="true"
                >
                    <?php echo e(__('More')); ?>

                    <svg class="h-4 w-4 transition-transform" :class="moreOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false" class="crm-360__more-menu" role="menu">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Crm\CustomerActivity::class)): ?>
                        <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('activities'); moreOpen = false"><?php echo e(__('Log activity')); ?></button>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                        <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('follow-ups'); moreOpen = false"><?php echo e(__('Schedule follow-up')); ?></button>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                        <a href="<?php echo e(route('admin.crm.leads.edit', $lead)); ?>" class="crm-360__more-item" role="menuitem" data-turbo-frame="erp-main" @click="moreOpen = false"><?php echo e(__('Edit lead')); ?></a>
                    <?php endif; ?>
                    <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('quotations'); moreOpen = false"><?php echo e(__('Quotation list')); ?></button>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                        <?php if($lead->status !== App\Enums\LeadStatus::Lost): ?>
                            <form method="POST" action="<?php echo e(route('admin.crm.leads.mark-lost', $lead)); ?>" class="crm-360__more-item p-0"><?php echo csrf_field(); ?>
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm" role="menuitem"><?php echo e(__('Mark lost')); ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <hr class="crm-360__more-divider">
                    <button type="button" class="crm-360__more-item w-full text-left" role="menuitem" @click="setTab('timeline'); moreOpen = false"><?php echo e(__('View timeline')); ?></button>
                </div>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\header.blade.php ENDPATH**/ ?>