<div class="crm-360__grid crm-360__grid--overview">
    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Customer Information')); ?></h2>
        <dl class="crm-360__dl">
            <div><dt><?php echo e(__('Type')); ?></dt><dd><?php echo e(ucfirst($customer->customer_type->value)); ?></dd></div>
            <div><dt><?php echo e(__('Contact person')); ?></dt><dd><?php echo e($customer->contact_person ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('Phone')); ?></dt><dd><?php echo e($customer->phone ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('Email')); ?></dt><dd><?php echo e($customer->email ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('City')); ?></dt><dd><?php echo e($customer->city ?: '—'); ?></dd></div>
            <div><dt><?php echo e(__('Credit limit')); ?></dt><dd><?php echo e($customer->credit_limit ? number_format((float) $customer->credit_limit, 2) : '—'); ?></dd></div>
        </dl>
        <?php if($customer->portalUser): ?>
            <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50/60 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-800"><?php echo e(__('Client portal')); ?></p>
                <dl class="crm-360__dl mt-2">
                    <div><dt><?php echo e(__('Portal user')); ?></dt><dd><?php echo e($customer->portalUser->name); ?></dd></div>
                    <div><dt><?php echo e(__('Login email')); ?></dt><dd><?php echo e($customer->portalUser->email); ?></dd></div>
                    <div>
                        <dt><?php echo e(__('Portal status')); ?></dt>
                        <dd><?php echo e($customer->portalUser->is_active ? __('Active') : __('Inactive')); ?></dd>
                    </div>
                    <?php
                        $lastPortalLogin = $customer->portalUser->sessions->sortByDesc('login_at')->first();
                    ?>
                    <div>
                        <dt><?php echo e(__('Last portal login')); ?></dt>
                        <dd><?php echo e($lastPortalLogin?->login_at?->diffForHumans() ?? '—'); ?></dd>
                    </div>
                </dl>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inviteToPortal', $customer)): ?>
                    <form method="POST" action="<?php echo e(route('admin.crm.customers.portal-invite', $customer)); ?>" class="mt-3" data-turbo-frame="_top">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--outline text-xs">
                            <?php echo e(__('Resend portal password link')); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php elseif(filled($customer->email)): ?>
            <div class="mt-4 rounded-lg border border-amber-100 bg-amber-50/70 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-900"><?php echo e(__('Client portal')); ?></p>
                <p class="mt-2 text-sm text-amber-950">
                    <?php echo e(__('This customer record has no portal login yet. Customers sign in at :url — invite them to create a password.', [
                        'url' => route('client.login'),
                    ])); ?>

                </p>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inviteToPortal', $customer)): ?>
                    <form method="POST" action="<?php echo e(route('admin.crm.customers.portal-invite', $customer)); ?>" class="mt-3" data-turbo-frame="_top">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--primary text-xs">
                            <?php echo e(__('Send client portal invite')); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-700"><?php echo e(__('Client portal')); ?></p>
                <p class="mt-2 text-sm text-slate-600">
                    <?php echo e(__('Add an email address to this customer profile before sending a portal invite.')); ?>

                </p>
            </div>
        <?php endif; ?>
        <?php if($customer->segments->isNotEmpty()): ?>
            <p class="mt-2 text-[11px] text-slate-500">
                <?php echo e(__('Segments')); ?>:
                <?php echo e($customer->segments->pluck('name')->join(', ')); ?>

            </p>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
            <div class="mt-3">
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.crm.customers.edit', $customer),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('View full profile')); ?> <?php echo $__env->renderComponent(); ?>
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
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Contact Summary')); ?></h2>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'sm','href' => route('admin.crm.customers.edit', $customer),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Manage contacts')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
        <ul class="crm-360__mini-list" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $customer->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <span class="font-medium text-erp-primary"><?php echo e($contact->name); ?></span>
                    <?php if($contact->is_primary): ?><span class="crm-360__pill"><?php echo e(__('Primary')); ?></span><?php endif; ?>
                    <span class="block text-[11px] text-slate-500"><?php echo e($contact->phone ?: $contact->email ?: '—'); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No contacts on file')); ?></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Recent Activity')); ?></h2>
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
            <?php $__empty_1 = true; $__currentLoopData = $customer->activities->sortByDesc('activity_at')->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $activity)): ?>
                        <a href="<?php echo e(route('admin.commercial.activities.show', $activity)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($activity->subject); ?></a>
                    <?php else: ?>
                        <span class="font-medium"><?php echo e($activity->subject); ?></span>
                    <?php endif; ?>
                    <span class="block text-[11px] text-slate-500"><?php echo e($activity->activity_at?->diffForHumans()); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No activities logged')); ?></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Recent Conversations')); ?></h2>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'conversations\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'conversations\')']); ?><?php echo e(__('View all')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $inboxConversations->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <a href="<?php echo e(route('admin.communications.inbox.index', ['conversation' => $conv->id])); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($conv->conversation_code); ?></a>
                    <span class="block text-[11px] text-slate-500"><?php echo e($conv->status->label()); ?> · <?php echo e($conv->last_activity_at?->diffForHumans()); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php $__empty_1 = true; $__currentLoopData = $whatsappConversations->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li>
                        <a href="<?php echo e(route('admin.communications.whatsapp.conversations.show', $conv)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($conv->conversation_code); ?></a>
                        <span class="block text-[11px] text-slate-500"><?php echo e(__('WhatsApp')); ?> · <?php echo e($conv->updated_at?->diffForHumans()); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="crm-360__empty-inline"><?php echo e(__('No conversations yet')); ?></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title"><?php echo e(__('Open Jobs')); ?></h2>
        <ul class="crm-360__mini-list" role="list">
            <?php $__empty_1 = true; $__currentLoopData = $openJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <?php if(Route::has('admin.production.job-cards.show')): ?>
                        <a href="<?php echo e(route('admin.production.job-cards.show', $job)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($job->job_card_number); ?></a>
                    <?php else: ?>
                        <span class="font-medium"><?php echo e($job->job_card_number); ?></span>
                    <?php endif; ?>
                    <span class="block text-[11px] text-slate-500"><?php echo e(\App\Support\EnumLabel::of($job->status)); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No open production jobs')); ?></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Outstanding Invoices')); ?></h2>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'commercial\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'commercial\')']); ?><?php echo e(__('View all')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $openInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($invoice->invoice_number); ?></a>
                    <span class="block text-[11px] text-slate-500"><?php echo e(number_format((float) $invoice->balance_due, 2)); ?> <?php echo e(__('due')); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="crm-360__empty-inline"><?php echo e(__('No outstanding invoices')); ?></li>
            <?php endif; ?>
        </ul>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-overview.blade.php ENDPATH**/ ?>