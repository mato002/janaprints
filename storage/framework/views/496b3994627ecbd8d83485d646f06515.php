<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Communications'),'heading' => __('Messages'),'fullMobileChat' => ! $show_history]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Communications')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Messages')),'fullMobileChat' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $show_history)]); ?>
    <div class="client-comms" data-client-comms>
        <?php if (! ($show_history)): ?>
            <section
                class="client-chat"
                aria-label="<?php echo e(__('Team chat')); ?>"
                data-client-chat
                data-feed-url="<?php echo e(route('client.communications.feed')); ?>"
                data-feed-fingerprint="<?php echo e($feed_fingerprint); ?>"
            >
                <header class="client-chat__head">
                    <div class="client-chat__identity">
                        <span class="client-chat__avatar" aria-hidden="true">JP</span>
                        <span class="client-chat__presence" title="<?php echo e(__('Team available')); ?>"></span>
                    </div>
                    <div class="client-chat__identity-text">
                        <h2 class="client-chat__title"><?php echo e(__('Jana Prints team')); ?></h2>
                        <p class="client-chat__meta"><?php echo e(__('Typically replies within business hours')); ?></p>
                    </div>
                    <div class="client-chat__head-actions">
                        <a
                            href="<?php echo e(route('client.communications.index', ['history' => 1])); ?>"
                            class="client-chat__history-link"
                            title="<?php echo e(__('Notification history')); ?>"
                        >
                            <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'inbox','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
                            <span class="sr-only"><?php echo e(__('Notification history')); ?></span>
                        </a>
                        <span class="client-chat__live" data-client-chat-live><?php echo e(__('Live')); ?></span>
                    </div>
                </header>

                <div class="client-chat__messages-pane">
                    <div class="client-chat__body" id="client-chat-scroll">
                        <?php echo $__env->make('client.communications.partials.chat-messages', ['events' => $feed], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>

                <?php echo $__env->make('client.communications.partials.composer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>
        <?php else: ?>
            <div class="client-comms__toolbar">
                <a
                    href="<?php echo e(route('client.communications.index')); ?>"
                    class="client-comms__history-toggle"
                >
                    <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'arrow-left','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-left','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
                    <span><?php echo e(__('Back to chat')); ?></span>
                </a>
            </div>

            <section class="client-panel client-panel--flush" aria-label="<?php echo e(__('Notification history')); ?>">
                <div class="client-table-wrap">
                    <table class="client-table">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Subject')); ?></th>
                                <th><?php echo e(__('Channel')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td data-label="<?php echo e(__('Date')); ?>"><?php echo e($log->created_at?->format('M j, Y H:i')); ?></td>
                                    <td data-label="<?php echo e(__('Category')); ?>"><?php echo e($communications->categoryLabel($log)); ?></td>
                                    <td data-label="<?php echo e(__('Subject')); ?>"><?php echo e($log->subject ?: \Illuminate\Support\Str::limit($log->message_body, 80)); ?></td>
                                    <td data-label="<?php echo e(__('Channel')); ?>"><?php echo e($log->channel?->label() ?? $log->channel); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="client-empty"><?php echo e(__('No notifications yet.')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php echo e($logs->links()); ?>

            </section>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\communications\index.blade.php ENDPATH**/ ?>