<?php
    $ordersCount = (int) ($commercial['counts']['orders'] ?? 0);
    $quotesCount = (int) ($commercial['counts']['quotations'] ?? 0);
    $openJobsCount = $openJobs->count();
    $outstanding = null;
    $complaintCount = null;
    $lastInteraction = null;
    $hasComplaintsKpi = false;
    foreach ($kpis as $kpiRow) {
        $key = $kpiRow['key'] ?? null;
        if ($key === 'balance') {
            $outstanding = $kpiRow['value'];
        } elseif ($key === 'complaints') {
            $hasComplaintsKpi = true;
            $complaintCount = (int) ($kpiRow['value'] ?? 0);
        } elseif ($key === 'activity' && ! empty($kpiRow['value'])) {
            $lastInteraction = $kpiRow['value'];
        }
    }

    $nextAction = __('Review customer profile and confirm contact details');
    $nextActionTab = 'overview';
    $nextActionKind = 'neutral';

    if (($outstanding ?? 0) > 0) {
        $nextAction = __('Follow up on outstanding balance of :amount', [
            'amount' => number_format((float) $outstanding, 2),
        ]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'attention';
    } elseif (($complaintCount ?? 0) > 0) {
        $nextAction = __('Address :count open complaint(s)', ['count' => $complaintCount]);
        $nextActionTab = 'overview';
        $nextActionKind = 'attention';
    } elseif ($openJobsCount > 0) {
        $nextAction = __('Monitor :count open production job(s)', ['count' => $openJobsCount]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif ($ordersCount > 0) {
        $nextAction = __('Follow up on :count sales order(s)', ['count' => $ordersCount]);
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif ($quotesCount > 0) {
        $nextAction = __('Advance open quotations');
        $nextActionTab = 'commercial';
        $nextActionKind = 'info';
    } elseif (auth()->user()->can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)) {
        $nextAction = __('Start a conversation to re-engage this customer');
        $nextActionTab = 'conversations';
        $nextActionKind = 'info';
    }

    $openWorkParts = collect([
        $ordersCount > 0 ? __(':count sales orders', ['count' => $ordersCount]) : null,
        $quotesCount > 0 ? __(':count quotes', ['count' => $quotesCount]) : null,
        $openJobsCount > 0 ? __(':count production jobs', ['count' => $openJobsCount]) : null,
    ])->filter();
?>

<div class="crm-360__overview">
    <div class="crm-360__overview-main">
        <section class="crm-360__panel-block crm-360__panel-block--primary">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title"><?php echo e(__('Customer profile')); ?></h2>
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
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Edit')); ?> <?php echo $__env->renderComponent(); ?>
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
            <dl class="crm-360__dl">
                <div><dt><?php echo e(__('Type')); ?></dt><dd><?php echo e(ucfirst($customer->customer_type->value)); ?></dd></div>
                <div><dt><?php echo e(__('Contact person')); ?></dt><dd><?php echo e($customer->contact_person ?: '—'); ?></dd></div>
                <div><dt><?php echo e(__('Phone')); ?></dt><dd><?php echo e($customer->phone ?: '—'); ?></dd></div>
                <div><dt><?php echo e(__('Email')); ?></dt><dd class="crm-360__truncate"><?php echo e($customer->email ?: '—'); ?></dd></div>
                <div><dt><?php echo e(__('City')); ?></dt><dd><?php echo e($customer->city ?: '—'); ?></dd></div>
                <div><dt><?php echo e(__('Credit limit')); ?></dt><dd><?php echo e($customer->credit_limit ? number_format((float) $customer->credit_limit, 2) : '—'); ?></dd></div>
            </dl>

            <?php if($customer->portalUser): ?>
                <div class="crm-360__inset crm-360__inset--info">
                    <p class="crm-360__inset-label"><?php echo e(__('Client portal')); ?></p>
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
                        <form method="POST" action="<?php echo e(route('admin.crm.customers.portal-invite', $customer)); ?>" class="mt-3" data-turbo-frame="erp-main">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--outline text-xs">
                                <?php echo e(__('Resend portal password link')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php elseif(filled($customer->email)): ?>
                <div class="crm-360__inset crm-360__inset--warn">
                    <p class="crm-360__inset-label"><?php echo e(__('Client portal')); ?></p>
                    <p class="mt-1.5 text-sm text-amber-950">
                        <?php echo e(__('This customer record has no portal login yet. Customers sign in at :url — invite them to create a password.', [
                            'url' => route('client.login'),
                        ])); ?>

                    </p>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inviteToPortal', $customer)): ?>
                        <form method="POST" action="<?php echo e(route('admin.crm.customers.portal-invite', $customer)); ?>" class="mt-3" data-turbo-frame="erp-main">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--primary text-xs">
                                <?php echo e(__('Send client portal invite')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="crm-360__inset">
                    <p class="crm-360__inset-label"><?php echo e(__('Client portal')); ?></p>
                    <p class="mt-1.5 text-sm text-slate-600">
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
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title"><?php echo e(__('Contacts')); ?></h2>
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
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Manage')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php if($customer->contacts->isNotEmpty()): ?>
                <ul class="crm-360__mini-list" role="list">
                    <?php $__currentLoopData = $customer->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <span class="font-medium text-erp-primary"><?php echo e($contact->name); ?></span>
                            <?php if($contact->is_primary): ?><span class="crm-360__pill"><?php echo e(__('Primary')); ?></span><?php endif; ?>
                            <span class="block text-[11px] text-slate-500"><?php echo e($contact->phone ?: $contact->email ?: '—'); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                <div class="crm-360__empty">
                    <p class="crm-360__empty-title"><?php echo e(__('No contacts added')); ?></p>
                    <p class="crm-360__empty-body"><?php echo e(__('Add the customer’s purchasing, finance or delivery contact.')); ?></p>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                        <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.crm.customers.edit', $customer),'class' => 'mt-2','dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'class' => 'mt-2','data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Add contact')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php endif; ?>
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title"><?php echo e(__('Active commercial work')); ?></h2>
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
            <div class="crm-360__split-lists">
                <div>
                    <p class="crm-360__subhead"><?php echo e(__('Sales orders')); ?></p>
                    <ul class="crm-360__mini-list" role="list">
                        <?php $__empty_1 = true; $__currentLoopData = $commercial['orders']->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li>
                                <?php if($row['url']): ?>
                                    <a href="<?php echo e($row['url']); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($row['number']); ?></a>
                                <?php else: ?>
                                    <span class="font-medium"><?php echo e($row['number']); ?></span>
                                <?php endif; ?>
                                <span class="block text-[11px] text-slate-500"><?php echo e($row['status']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="crm-360__empty-inline"><?php echo e(__('No sales orders yet')); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <p class="crm-360__subhead"><?php echo e(__('Open jobs')); ?></p>
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
                </div>
            </div>
        </section>

        <section class="crm-360__panel-block">
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
            <?php
                $recentActivities = $customer->activities->sortByDesc('activity_at')->take(4);
                $hasInbox = $inboxConversations->isNotEmpty();
                $hasWhatsapp = $whatsappConversations->isNotEmpty();
            ?>
            <?php if($recentActivities->isNotEmpty() || $hasInbox || $hasWhatsapp): ?>
                <ul class="crm-360__mini-list" role="list">
                    <?php $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $activity)): ?>
                                <a href="<?php echo e(route('admin.commercial.activities.show', $activity)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($activity->subject); ?></a>
                            <?php else: ?>
                                <span class="font-medium"><?php echo e($activity->subject); ?></span>
                            <?php endif; ?>
                            <span class="block text-[11px] text-slate-500"><?php echo e($activity->activity_at?->diffForHumans()); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__empty_1 = true; $__currentLoopData = $inboxConversations->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li>
                            <a href="<?php echo e(route('admin.communications.inbox.index', ['conversation' => $conv->id])); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($conv->conversation_code); ?></a>
                            <span class="block text-[11px] text-slate-500"><?php echo e($conv->status->label()); ?> · <?php echo e($conv->last_activity_at?->diffForHumans()); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php $__currentLoopData = $whatsappConversations->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e(route('admin.communications.whatsapp.conversations.show', $conv)); ?>" class="crm-360__row-link" data-turbo-frame="erp-main"><?php echo e($conv->conversation_code); ?></a>
                                <span class="block text-[11px] text-slate-500"><?php echo e(__('WhatsApp')); ?> · <?php echo e($conv->updated_at?->diffForHumans()); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <div class="crm-360__empty">
                    <p class="crm-360__empty-title"><?php echo e(__('No recent activity')); ?></p>
                    <p class="crm-360__empty-body"><?php echo e(__('Calls, notes, messages and order updates will appear here.')); ?></p>
                    <div class="crm-360__empty-actions">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'outline','size' => 'sm','@click' => 'setTab(\'notes\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'outline','size' => 'sm','@click' => 'setTab(\'notes\')']); ?><?php echo e(__('Add note')); ?> <?php echo $__env->renderComponent(); ?>
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
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                            <form method="POST" action="<?php echo e(route('admin.communications.inbox.customers.start', $customer)); ?>" data-turbo-frame="erp-main">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm"><?php echo e(__('Start conversation')); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="crm-360__overview-aside">
        <section class="crm-360__panel-block crm-360__panel-block--health">
            <h2 class="crm-360__card-title"><?php echo e(__('Relationship summary')); ?></h2>
            <dl class="crm-360__health-list">
                <div>
                    <dt><?php echo e(__('Customer health')); ?></dt>
                    <dd>
                        <span class="crm-360__status crm-360__status--<?php echo e($customer->status->value); ?> crm-360__status--inline">
                            <?php echo e(ucfirst($customer->status->value)); ?>

                        </span>
                    </dd>
                </div>
                <div>
                    <dt><?php echo e(__('Last interaction')); ?></dt>
                    <dd><?php echo e($lastInteraction ? $lastInteraction->diffForHumans() : '—'); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Open work')); ?></dt>
                    <dd><?php echo e($openWorkParts->isNotEmpty() ? $openWorkParts->join(', ') : __('None')); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Outstanding')); ?></dt>
                    <dd class="<?php echo e(($outstanding ?? 0) > 0 ? 'crm-360__value--alert' : ''); ?>">
                        <?php echo e($outstanding !== null ? number_format((float) $outstanding, 2) : '—'); ?>

                    </dd>
                </div>
                <?php if($hasComplaintsKpi): ?>
                    <div>
                        <dt><?php echo e(__('Complaints')); ?></dt>
                        <dd class="<?php echo e(($complaintCount ?? 0) > 0 ? 'crm-360__value--alert' : ''); ?>">
                            <?php echo e(($complaintCount ?? 0) > 0 ? __(':count open', ['count' => $complaintCount]) : __('None open')); ?>

                        </dd>
                    </div>
                <?php endif; ?>
                <?php if($canJobs): ?>
                    <div>
                        <dt><?php echo e(__('Production jobs')); ?></dt>
                        <dd><?php echo e($openJobsCount > 0 ? __(':count open', ['count' => $openJobsCount]) : __('None open')); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>

        <section class="crm-360__panel-block crm-360__panel-block--next crm-360__panel-block--<?php echo e($nextActionKind); ?>">
            <h2 class="crm-360__card-title"><?php echo e(__('Next action')); ?></h2>
            <p class="crm-360__next-copy"><?php echo e($nextAction); ?></p>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'primary','size' => 'sm','class' => 'mt-3','@click' => 'setTab(@js($nextActionTab))']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'primary','size' => 'sm','class' => 'mt-3','@click' => 'setTab(@js($nextActionTab))']); ?>
                <?php echo e(__('Go')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        </section>

        <section class="crm-360__panel-block">
            <div class="crm-360__card-head">
                <h2 class="crm-360__card-title"><?php echo e(__('Financial snapshot')); ?></h2>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'commercial\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'ghost','size' => 'sm','@click' => 'setTab(\'commercial\')']); ?><?php echo e(__('Details')); ?> <?php echo $__env->renderComponent(); ?>
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

        <section class="crm-360__panel-block crm-360__panel-block--meta">
            <h2 class="crm-360__card-title"><?php echo e(__('Account')); ?></h2>
            <dl class="crm-360__health-list">
                <div>
                    <dt><?php echo e(__('Branch')); ?></dt>
                    <dd><?php echo e($customer->branch?->name ?? '—'); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Status')); ?></dt>
                    <dd><?php echo e(ucfirst($customer->status->value)); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Customer since')); ?></dt>
                    <dd><?php echo e($customer->created_at?->format('M Y') ?? '—'); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Code')); ?></dt>
                    <dd class="font-mono"><?php echo e($customer->customer_code); ?></dd>
                </div>
            </dl>
        </section>
    </aside>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-overview.blade.php ENDPATH**/ ?>