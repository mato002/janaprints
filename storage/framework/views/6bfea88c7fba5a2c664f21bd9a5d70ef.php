<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $lead->lead_name,'breadcrumbs' => [['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => $lead->lead_name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="lead-360">
        <header class="lead-360__header">
            <div class="lead-360__identity">
                <h1 class="lead-360__title"><?php echo e($lead->lead_name); ?></h1>
                <?php if($lead->company_name): ?>
                    <p class="lead-360__subtitle"><?php echo e($lead->company_name); ?></p>
                <?php endif; ?>
                <div class="lead-360__meta">
                    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'info']); ?><?php echo e($lead->status->value); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                    <span class="lead-360__meta-item"><?php echo e($lead->stage?->name ?? __('No stage')); ?></span>
                    <span class="lead-360__meta-item"><?php echo e(__('Value')); ?>: <?php echo e(number_format((float) $lead->estimated_value, 2)); ?></span>
                </div>
            </div>

            <div class="lead-360__actions">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quote', $lead)): ?>
                    <a href="<?php echo e(route('admin.crm.leads.quotation.create', $lead)); ?>" class="crm-360__btn crm-360__btn--primary" data-turbo-frame="erp-main">
                        <?php echo e(__('Create Quotation')); ?>

                    </a>
                    <form method="POST" action="<?php echo e(route('admin.crm.leads.quotation.quick', $lead)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--outline"><?php echo e(__('Quick Quote')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($lead->customer_id && $lead->customer): ?>
                    <a href="<?php echo e(route('admin.crm.customers.show', $lead->customer)); ?>" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main"><?php echo e(__('Open Customer')); ?></a>
                <?php elseif(auth()->user()?->can('convert', $lead)): ?>
                    <form method="POST" action="<?php echo e(route('admin.crm.leads.convert', $lead)); ?>" class="inline"><?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--outline"><?php echo e(__('Convert to Customer')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                    <a href="<?php echo e(route('admin.crm.leads.edit', $lead)); ?>" class="crm-360__btn crm-360__btn--ghost" data-turbo-frame="erp-main"><?php echo e(__('Edit Lead')); ?></a>
                <?php endif; ?>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <section class="lead-360__card">
                    <h2 class="lead-360__card-title"><?php echo e(__('Lead Snapshot')); ?></h2>
                    <div class="lead-360__grid">
                        <div><span class="lead-360__label"><?php echo e(__('Phone')); ?></span><p><?php echo e($lead->phone ?: '—'); ?></p></div>
                        <div><span class="lead-360__label"><?php echo e(__('Email')); ?></span><p><?php echo e($lead->email ?: '—'); ?></p></div>
                        <div><span class="lead-360__label"><?php echo e(__('Source')); ?></span><p><?php echo e($lead->leadSource?->name ?? '—'); ?></p></div>
                        <div><span class="lead-360__label"><?php echo e(__('Assigned To')); ?></span><p><?php echo e($lead->assignee?->name ?? __('Unassigned')); ?></p></div>
                        <div><span class="lead-360__label"><?php echo e(__('Probability')); ?></span><p><?php echo e($lead->probability !== null ? $lead->probability.'%' : '—'); ?></p></div>
                        <div><span class="lead-360__label"><?php echo e(__('Expected Close')); ?></span><p><?php echo e($lead->expected_close_date?->format('d M Y') ?? '—'); ?></p></div>
                    </div>
                    <?php if($lead->notes): ?>
                        <div class="lead-360__notes">
                            <span class="lead-360__label"><?php echo e(__('Notes')); ?></span>
                            <p class="whitespace-pre-wrap"><?php echo e($lead->notes); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="lead-360__card">
                    <div class="lead-360__card-head">
                        <h2 class="lead-360__card-title"><?php echo e(__('Quotations')); ?></h2>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Sales\Quotation::class)): ?>
                            <a href="<?php echo e(route('admin.quotations.index', ['lead_id' => $lead->id])); ?>" class="text-xs font-semibold text-indigo-700 hover:underline" data-turbo-frame="erp-main">
                                <?php echo e(__('View all')); ?>

                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if($lead->quotations->isNotEmpty()): ?>
                        <ul class="lead-360__quote-list" role="list">
                            <?php $__currentLoopData = $lead->quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('admin.quotations.show', $quotation)); ?>" class="lead-360__quote-row" data-turbo-frame="erp-main">
                                        <span class="font-mono text-sm font-semibold text-erp-primary"><?php echo e($quotation->quotation_number); ?></span>
                                        <span class="text-xs text-slate-500"><?php echo e($quotation->quotation_date?->format('d M Y')); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'neutral']); ?><?php echo e($quotation->status->value); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                        <span class="text-sm font-medium tabular-nums"><?php echo e(number_format((float) $quotation->total_amount, 2)); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No quotations linked to this lead yet.')); ?></p>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quote', $lead)): ?>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="<?php echo e(route('admin.crm.leads.quotation.create', $lead)); ?>" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" data-turbo-frame="erp-main"><?php echo e(__('Create Quotation')); ?></a>
                                <form method="POST" action="<?php echo e(route('admin.crm.leads.quotation.quick', $lead)); ?>"><?php echo csrf_field(); ?>
                                    <button type="submit" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"><?php echo e(__('Quick Quote')); ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

                <section class="lead-360__card">
                    <h2 class="lead-360__card-title"><?php echo e(__('Follow-ups')); ?></h2>
                    <?php $__currentLoopData = $lead->followUps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0">
                            <span><?php echo e($fu->scheduled_at->format('Y-m-d H:i')); ?> — <?php echo e($fu->status->value); ?></span>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                                <form method="POST" action="<?php echo e(route('admin.crm.leads.follow-ups.update', [$lead, $fu])); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <input type="hidden" name="status" value="completed">
                                    <button class="text-xs font-semibold text-emerald-700"><?php echo e(__('Complete')); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                        <form method="POST" action="<?php echo e(route('admin.crm.leads.follow-ups.store', $lead)); ?>" class="mt-4 space-y-2"><?php echo csrf_field(); ?>
                            <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['name' => 'scheduled_at','type' => 'datetime-local','class' => 'w-full','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'scheduled_at','type' => 'datetime-local','class' => 'w-full','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                            <textarea name="notes" class="erp-input w-full text-sm" rows="2"></textarea>
                            <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-xs']); ?><?php echo e(__('Schedule follow-up')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                        </form>
                    <?php endif; ?>
                </section>
            </div>

            <aside class="space-y-4 xl:col-span-4 xl:sticky xl:top-20">
                <section class="lead-360__card">
                    <h2 class="lead-360__card-title"><?php echo e(__('Conversion')); ?></h2>
                    <dl class="lead-360__rail">
                        <div>
                            <dt><?php echo e(__('Customer')); ?></dt>
                            <dd><?php echo e($lead->customer ? $lead->customer->company_name : __('Not linked')); ?></dd>
                        </div>
                        <div>
                            <dt><?php echo e(__('Quotations')); ?></dt>
                            <dd><?php echo e($lead->quotations->count()); ?></dd>
                        </div>
                    </dl>
                </section>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
                    <?php if($lead->status !== App\Enums\LeadStatus::Lost): ?>
                        <form method="POST" action="<?php echo e(route('admin.crm.leads.mark-lost', $lead)); ?>"><?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--outline w-full justify-center"><?php echo e(__('Mark Lost')); ?></button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $lead)): ?>
                    <form method="POST" action="<?php echo e(route('admin.crm.leads.destroy', $lead)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this lead?'))->toHtml() ?>)"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--danger w-full justify-center"><?php echo e(__('Delete Lead')); ?></button>
                    </form>
                <?php endif; ?>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/leads/show.blade.php ENDPATH**/ ?>