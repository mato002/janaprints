<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Event')); ?></span>
            <select name="event_code" class="erp-input w-full" required <?php if($rule?->is_system): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($event->value); ?>" <?php if(old('event_code', $rule?->event_code) === $event->value): echo 'selected'; endif; ?>>
                        <?php echo e($event->label()); ?> (<?php echo e($event->value); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($rule?->is_system): ?>
                <input type="hidden" name="event_code" value="<?php echo e($rule->event_code); ?>">
            <?php endif; ?>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Template')); ?></span>
            <select name="posting_template_id" class="erp-input w-full" required>
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($template->id); ?>" <?php if((int) old('posting_template_id', $rule?->posting_template_id) === $template->id): echo 'selected'; endif; ?>>
                        <?php echo e($template->code); ?> — <?php echo e($template->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Name')); ?></span>
            <input name="name" value="<?php echo e(old('name', $rule?->name)); ?>" class="erp-input w-full">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Priority')); ?></span>
            <input type="number" name="priority" value="<?php echo e(old('priority', $rule?->priority ?? 100)); ?>" class="erp-input w-full" min="1" max="9999">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Active')); ?></span>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="erp-checkbox" <?php if(old('is_active', $rule?->is_active ?? true)): echo 'checked'; endif; ?>>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Auto post')); ?></span>
            <input type="hidden" name="auto_post" value="0">
            <input type="checkbox" name="auto_post" value="1" class="erp-checkbox" <?php if(old('auto_post', $rule?->auto_post ?? true)): echo 'checked'; endif; ?>>
        </label>
        <label class="block text-sm sm:col-span-2">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Description')); ?></span>
            <textarea name="description" rows="2" class="erp-input w-full"><?php echo e(old('description', $rule?->description)); ?></textarea>
        </label>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\posting\rules\partials\form.blade.php ENDPATH**/ ?>