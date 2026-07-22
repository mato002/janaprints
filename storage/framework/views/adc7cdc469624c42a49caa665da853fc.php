<div class="crm-360__grid crm-360__grid--overview">
    <?php if(! empty($acquisition)): ?>
        <section class="crm-360__card crm-360__card--full">
            <h2 class="crm-360__card-title"><?php echo e(__('Acquisition intake')); ?></h2>
            <dl class="crm-360__dl">
                <div><dt><?php echo e(__('Origin')); ?></dt><dd><?php echo e($acquisition['origin']); ?></dd></div>
                <div><dt><?php echo e(__('Reference')); ?></dt>
                    <dd>
                        <?php if($acquisition['url']): ?>
                            <a href="<?php echo e($acquisition['url']); ?>" class="text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($acquisition['reference']); ?></a>
                        <?php else: ?>
                            <?php echo e($acquisition['reference']); ?>

                        <?php endif; ?>
                    </dd>
                </div>
                <div><dt><?php echo e(__('Requested product')); ?></dt><dd><?php echo e($acquisition['requested_product']); ?></dd></div>
                <div><dt><?php echo e(__('Quantity')); ?></dt><dd><?php echo e($acquisition['quantity'] ?: '—'); ?></dd></div>
                <div><dt><?php echo e(__('Budget')); ?></dt><dd><?php echo e($acquisition['budget'] ? number_format((float) $acquisition['budget'], 2) : '—'); ?></dd></div>
                <div><dt><?php echo e(__('Deadline')); ?></dt><dd><?php echo e($acquisition['deadline'] ?: '—'); ?></dd></div>
            </dl>
            <?php if(! empty($acquisition['attachments'])): ?>
                <div class="mt-4">
                    <p class="text-sm font-medium text-slate-700"><?php echo e(__('Attachments')); ?></p>
                    <ul class="mt-2 space-y-2 text-sm">
                        <?php $__currentLoopData = $acquisition['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex flex-wrap items-center gap-2">
                                <span><?php echo e($attachment['name']); ?></span>
                                <?php if(! empty($attachment['preview_url'])): ?>
                                    <a href="<?php echo e($attachment['preview_url']); ?>" class="text-erp-accent hover:underline" target="_blank" rel="noopener"><?php echo e(__('Preview')); ?></a>
                                <?php endif; ?>
                                <?php if(! empty($attachment['download_url'])): ?>
                                    <a href="<?php echo e($attachment['download_url']); ?>" class="text-erp-accent hover:underline"><?php echo e(__('Download')); ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Opportunity overview')); ?></h2>
        <dl class="crm-360__dl">
            <div><dt><?php echo e(__('Lead source')); ?></dt><dd><?php echo e($lead->leadSource?->name ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Stage')); ?></dt><dd><?php echo e($lead->stage?->name ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Status')); ?></dt><dd><?php echo e(str_replace('_', ' ', $lead->status->value)); ?></dd></div>
            <div><dt><?php echo e(__('Assigned user')); ?></dt><dd><?php echo e($lead->assignee?->name ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Estimated value')); ?></dt><dd><?php echo e(number_format((float) $lead->estimated_value, 2)); ?></dd></div>
            <div><dt><?php echo e(__('Probability')); ?></dt><dd><?php echo e($lead->probability !== null ? $lead->probability.'%' : '—'); ?></dd></div>
            <div><dt><?php echo e(__('Expected close date')); ?></dt><dd><?php echo e($lead->expected_close_date?->format('d M Y') ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Customer link')); ?></dt>
                <dd>
                    <?php if($lead->customer): ?>
                        <a href="<?php echo e(route('admin.crm.customers.show', $lead->customer)); ?>" class="text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e($lead->customer->company_name); ?></a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
            </div>
        </dl>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
            <div class="mt-3">
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.crm.leads.edit', $lead),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.leads.edit', $lead)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Edit lead')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Contact details')); ?></h2>
        <dl class="crm-360__dl">
            <div><dt><?php echo e(__('Company')); ?></dt><dd><?php echo e($lead->company_name ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('Contact')); ?></dt><dd><?php echo e($lead->lead_name); ?></dd></div>
            <div><dt><?php echo e(__('Phone')); ?></dt><dd><?php echo e($lead->phone ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('Email')); ?></dt><dd><?php echo e($lead->email ?: '—'); ?></dd></div>
        </dl>
        <?php if($lead->notes): ?>
            <p class="mt-3 text-sm text-slate-600"><span class="font-medium"><?php echo e(__('Notes')); ?>:</span> <?php echo e($lead->notes); ?></p>
        <?php endif; ?>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Recent activity')); ?></h2>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'activities\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'activities\')']); ?><?php echo e(__('View all')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        </div>
        <ul class="crm-360__mini-list" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $lead->activities->sortByDesc('activity_at')->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <span class="font-medium text-erp-primary"><?php echo e($activity->subject); ?></span>
                    <span class="block text-[11px] text-slate-500"><?php echo e(ucfirst(str_replace('_', ' ', $activity->activity_type->value))); ?> · <?php echo e($activity->activity_at?->diffForHumans()); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No activities logged yet')); ?></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Upcoming follow-ups')); ?></h2>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'follow-ups\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'follow-ups\')']); ?><?php echo e(__('Manage')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        </div>
        <ul class="crm-360__mini-list" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $followUps['scheduled']->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $followUp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <span class="font-medium text-erp-primary"><?php echo e($followUp['scheduled_at']?->format('d M Y H:i')); ?></span>
                    <span class="block text-[11px] text-slate-500"><?php echo e($followUp['notes'] ?: __('Scheduled follow-up')); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No scheduled follow-ups')); ?></li>
            <?php endif; ?>
        </ul>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-overview.blade.php ENDPATH**/ ?>