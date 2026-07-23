<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $campaign->name,'breadcrumbs' => [['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => $campaign->name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.sms.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $campaign->name,'description' => $campaign->campaign_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->campaign_code)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex flex-wrap gap-2">
                <?php if($campaign->status->canEdit()): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $campaign)): ?>
                        <a href="<?php echo e(route('admin.communications.sms.campaigns.edit', $campaign)); ?>" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main"><?php echo e(__('Edit')); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($campaign->status->canQueue()): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $campaign)): ?>
                        <?php if (! ($campaign->approved_at)): ?>
                            <form method="POST" action="<?php echo e(route('admin.communications.sms.campaigns.approve', $campaign)); ?>"><?php echo csrf_field(); ?>
                                <button class="erp-btn erp-btn--secondary"><?php echo e(__('Approve')); ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send', $campaign)): ?>
                        <form method="POST" action="<?php echo e(route('admin.communications.sms.campaigns.send', $campaign)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Queue this campaign for background sending?'))->toHtml() ?>)"><?php echo csrf_field(); ?>
                            <button class="erp-btn erp-btn--primary"><?php echo e($campaign->send_mode === \App\Enums\SmsCampaignSendMode::Scheduled ? __('Schedule send') : __('Send now')); ?></button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($campaign->status->canCancel()): ?>
                    <form method="POST" action="<?php echo e(route('admin.communications.sms.campaigns.cancel', $campaign)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn erp-btn--ghost text-red-700"><?php echo e(__('Cancel')); ?></button>
                    </form>
                <?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Status'),'value' => $campaign->status->label()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->status->label())]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Recipients'),'value' => $campaign->total_recipients]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Recipients')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->total_recipients)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Est. segments'),'value' => $campaign->estimated_segments]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Est. segments')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($campaign->estimated_segments)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => __('Est. cost'),'value' => number_format($campaign->estimated_cost, 2)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Est. cost')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($campaign->estimated_cost, 2))]); ?>
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

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Message')); ?></h2>
            <pre class="mt-2 whitespace-pre-wrap rounded border border-erp-border bg-slate-50 p-3 text-sm"><?php echo e($campaign->message_template); ?></pre>
            <?php if($campaign->template): ?>
                <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Template')); ?>: <?php echo e($campaign->template->name); ?></p>
            <?php endif; ?>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title"><?php echo e(__('Campaign audit')); ?></h2>
            <dl class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Created by')); ?></dt><dd><?php echo e($campaign->creator?->name); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Approved by')); ?></dt><dd><?php echo e($campaign->approver?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Sent by')); ?></dt><dd><?php echo e($campaign->sender?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Branch')); ?></dt><dd><?php echo e($campaign->branch?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Department')); ?></dt><dd><?php echo e($campaign->department?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Queued')); ?></dt><dd><?php echo e($campaign->queued_at?->format('d M Y H:i') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Completed')); ?></dt><dd><?php echo e($campaign->completed_at?->format('d M Y H:i') ?? '—'); ?></dd></div>
            </dl>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h2 class="erp-card-title"><?php echo e(__('Recipients')); ?> (<?php echo e($campaign->recipients->count()); ?>)</h2>
        <div class="mt-2 max-h-64 overflow-y-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Name')); ?></th><th><?php echo e(__('Phone')); ?></th><th><?php echo e(__('Source')); ?></th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $campaign->recipients->take(100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recipient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($recipient->display_name); ?></td>
                            <td class="font-mono text-xs"><?php echo e($recipient->phone_number); ?></td>
                            <td><?php echo e($recipient->source_type); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\sms\campaigns\show.blade.php ENDPATH**/ ?>