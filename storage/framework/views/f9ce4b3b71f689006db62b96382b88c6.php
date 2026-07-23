<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $conversation->conversation_code,'breadcrumbs' => [['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => $conversation->conversation_code]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.whatsapp.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $conversation->customer?->name ?? $conversation->phone_number,'description' => $conversation->conversation_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conversation->customer?->name ?? $conversation->phone_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conversation->conversation_code)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="rounded px-2 py-1 text-xs font-semibold uppercase <?php echo e($conversation->status->badgeClass()); ?>"><?php echo e($conversation->status->label()); ?></span>
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

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 erp-card flex flex-col min-h-[24rem]">
            <div class="flex-1 space-y-3 overflow-y-auto max-h-[32rem] p-4">
                <?php $__currentLoopData = $conversation->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex <?php echo e($msg->direction->value === 'outgoing' ? 'justify-end' : 'justify-start'); ?>">
                        <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm <?php echo e($msg->direction->value === 'outgoing' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-800'); ?>">
                            <p class="whitespace-pre-wrap"><?php echo e($msg->body); ?></p>
                            <p class="mt-1 text-[10px] opacity-75">
                                <?php echo e($msg->message_type->label()); ?> · <?php echo e($msg->status->label()); ?> · <?php echo e($msg->created_at->format('H:i')); ?>

                            </p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send', App\Models\Communications\WhatsappConversation::class)): ?>
                <form method="POST" action="<?php echo e(route('admin.communications.whatsapp.conversations.messages.store', $conversation)); ?>" class="border-t p-3 space-y-2">
                    <?php echo csrf_field(); ?>
                    <textarea name="body" rows="2" class="erp-input w-full" placeholder="<?php echo e(__('Type a message…')); ?>"></textarea>
                    <?php if($templates->isNotEmpty()): ?>
                        <select name="whatsapp_template_id" class="erp-input w-full text-sm">
                            <option value=""><?php echo e(__('Or send COM-1 template…')); ?></option>
                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->communicationTemplate->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                    <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm"><?php echo e(__('Send')); ?></button>
                </form>
            <?php endif; ?>
        </div>
        <div class="space-y-4">
            <div class="erp-card text-sm space-y-2">
                <h3 class="erp-card-title"><?php echo e(__('Details')); ?></h3>
                <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Phone')); ?></span><span><?php echo e($conversation->phone_number); ?></span></div>
                <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Account')); ?></span><span><?php echo e($conversation->account?->name); ?></span></div>
                <div class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Assignee')); ?></span><span><?php echo e($conversation->assignee?->name ?? '—'); ?></span></div>
                <?php if($conversation->tags): ?>
                    <div class="flex flex-wrap gap-1 pt-1">
                        <?php $__currentLoopData = $conversation->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs"><?php echo e($tag); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\CommunicationLog::class)): ?>
                <?php
                    $waLogs = app(\App\Support\Communications\CommunicationLogService::class)
                        ->forEntity('customer', $conversation->customer_id ?? 0, $conversation->company_id, 10, \App\Enums\CommunicationLogChannel::WhatsApp);
                ?>
                <?php if($conversation->customer_id): ?>
                    <div class="erp-card">
                        <h3 class="erp-card-title"><?php echo e(__('COM-4 WhatsApp log')); ?></h3>
                        <?php if (isset($component)) { $__componentOriginalf31cb82ccd763b22007007c4a1985569 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf31cb82ccd763b22007007c4a1985569 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.communication-timeline','data' => ['logs' => $waLogs,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.communication-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($waLogs),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf31cb82ccd763b22007007c4a1985569)): ?>
<?php $attributes = $__attributesOriginalf31cb82ccd763b22007007c4a1985569; ?>
<?php unset($__attributesOriginalf31cb82ccd763b22007007c4a1985569); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf31cb82ccd763b22007007c4a1985569)): ?>
<?php $component = $__componentOriginalf31cb82ccd763b22007007c4a1985569; ?>
<?php unset($__componentOriginalf31cb82ccd763b22007007c4a1985569); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\whatsapp\conversations\show.blade.php ENDPATH**/ ?>