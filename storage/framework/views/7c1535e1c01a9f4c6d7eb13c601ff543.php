<footer class="client-chat__composer" data-client-chat-composer>
    <form
        method="POST"
        action="<?php echo e(route('client.communications.messages.store')); ?>"
        class="client-chat__form"
        data-client-chat-form
        data-turbo="false"
    >
        <?php echo csrf_field(); ?>
        <label class="sr-only" for="client-chat-body"><?php echo e(__('Message')); ?></label>
        <input
            id="client-chat-file"
            type="file"
            class="sr-only"
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
            data-client-chat-file
        >

        <div class="client-chat__composer-bar">
            <button type="button" class="client-chat__tool" data-client-chat-attach title="<?php echo e(__('Attach file')); ?>" aria-label="<?php echo e(__('Attach file')); ?>">
                <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'paperclip','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'paperclip','class' => 'h-5 w-5']); ?>
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
            </button>

            <div class="client-chat__field">
                <div class="client-chat__reply-preview hidden" data-client-chat-reply-preview hidden>
                    <div class="client-chat__reply-preview-bar" aria-hidden="true"></div>
                    <div class="client-chat__reply-preview-body">
                        <p class="client-chat__reply-preview-author" data-client-chat-reply-author></p>
                        <p class="client-chat__reply-preview-text" data-client-chat-reply-text></p>
                    </div>
                    <button type="button" class="client-chat__reply-preview-cancel" data-client-chat-reply-cancel aria-label="<?php echo e(__('Cancel reply')); ?>">
                        <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'x','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','class' => 'h-4 w-4']); ?>
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
                    </button>
                </div>
                <textarea
                    id="client-chat-body"
                    name="body"
                    rows="1"
                    class="client-chat__input"
                    placeholder="<?php echo e(__('Write a message…')); ?>"
                    data-client-chat-body
                ></textarea>
                <div class="client-chat__file-chip hidden" data-client-chat-file-chip hidden>
                    <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'document','class' => 'h-4 w-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document','class' => 'h-4 w-4 shrink-0']); ?>
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
                    <span data-client-chat-file-name></span>
                    <button type="button" class="client-chat__file-clear" data-client-chat-file-clear aria-label="<?php echo e(__('Remove file')); ?>">
                        <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'x','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','class' => 'h-3.5 w-3.5']); ?>
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
                    </button>
                </div>
            </div>

            <button type="submit" class="client-chat__send" title="<?php echo e(__('Send')); ?>" aria-label="<?php echo e(__('Send')); ?>" data-client-chat-send>
                <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'send','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'send','class' => 'h-5 w-5']); ?>
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
            </button>
        </div>
    </form>

    <form
        method="POST"
        action="<?php echo e(route('client.communications.attachments.store')); ?>"
        enctype="multipart/form-data"
        class="hidden"
        data-client-chat-attachment-form
        data-turbo="false"
    >
        <?php echo csrf_field(); ?>
        <input type="hidden" name="caption" data-client-chat-attachment-caption>
        <input type="file" name="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" data-client-chat-attachment-file>
    </form>
</footer>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\communications\partials\composer.blade.php ENDPATH**/ ?>